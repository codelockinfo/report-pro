import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import path from 'path';
import fs from 'fs';
import { shopifyApi, LATEST_API_VERSION } from '@shopify/shopify-api';
import '@shopify/shopify-api/adapters/node';

import { setupRoutes } from './routes';
import { createInstallRouter } from './routes/install';
import { initializeDatabase } from './database/connection';
import { initializeRedis } from './services/redis';
import { initializeQueue } from './services/queue';
import { createEmbeddedAppAuth } from './middleware/embeddedAppAuth';
import { sessionStorage } from './services/sessionStorage';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Initialize Shopify API
// Note: For embedded apps, session verification happens automatically
// when using shopify.auth.begin and shopify.auth.callback
// Session storage is required for embedded app authentication
const shopify = shopifyApi({
  apiKey: process.env.SHOPIFY_API_KEY!,
  apiSecretKey: process.env.SHOPIFY_API_SECRET!,
  scopes: process.env.SHOPIFY_SCOPES?.split(',') || [],
  hostName: process.env.SHOPIFY_APP_URL?.replace(/https?:\/\//, '') || 'localhost:3000',
  apiVersion: LATEST_API_VERSION,
  isEmbeddedApp: true,
  sessionStorage: sessionStorage,
});

// Middleware
app.use(cors({
  origin: true,
  credentials: true,
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Content Security Policy for Shopify embedded apps
app.use((req, res, next) => {
  // Allow Shopify to embed the app in iframe
  // Note: frame-ancestors in CSP replaces X-Frame-Options
  // Allow all Shopify admin domains
  res.setHeader(
    'Content-Security-Policy',
    "frame-ancestors 'self' https://*.myshopify.com https://admin.shopify.com https://*.admin.shopify.com;"
  );
  // Also set X-Frame-Options for older browsers (but CSP takes precedence)
  res.removeHeader('X-Frame-Options');
  next();
});

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Diagnostic endpoint for embedded app debugging
app.get('/api/diagnostic', (req, res) => {
  res.json({
    status: 'ok',
    embedded: true,
    apiKey: process.env.SHOPIFY_API_KEY ? 'Set' : 'Missing',
    appUrl: process.env.SHOPIFY_APP_URL || 'Not set',
    request: {
      path: req.path,
      query: req.query,
      headers: {
        'user-agent': req.headers['user-agent'],
        'referer': req.headers['referer'],
        'origin': req.headers['origin'],
      }
    }
  });
});

// Embedded app authentication middleware
// This handles session verification for embedded app requests
app.use(createEmbeddedAppAuth(shopify));

// Installation route (handles initial app access and OAuth redirect)
// Must come after embedded app auth to avoid conflicts
app.use('/', createInstallRouter());

// Setup API routes
setupRoutes(app, shopify);

// Serve static files from web/dist in production
// IMPORTANT: This must come AFTER API routes but handle all non-API requests
if (process.env.NODE_ENV === 'production') {
  // Handle both development (server/dist) and deployment (dist/server) scenarios
  // When running from dist/server/server.js, frontend is at ../web/ (not ../web/dist)
  // When running from server/dist/server.js, frontend is at ../../web/dist
  const possiblePaths = [
    path.join(__dirname, '..', 'web'),  // dist/server -> dist/web (deployment)
    path.join(__dirname, '..', 'web', 'dist'),  // dist/server -> dist/web/dist (fallback)
    path.join(__dirname, '..', '..', 'web', 'dist'),  // server/dist -> web/dist (development)
  ];
  
  let frontendDistPath = possiblePaths.find(p => fs.existsSync(p));
  if (!frontendDistPath) {
    frontendDistPath = possiblePaths[0]; // Default to first path
    console.warn(`[Warning] Frontend path not found, using default: ${frontendDistPath}`);
  }
  
  // Serve static assets first (CSS, JS, images, etc.)
  app.use(express.static(frontendDistPath, {
    maxAge: '1y', // Cache static assets
    etag: true,
  }));
  
  // Serve index.html for all non-API routes (SPA routing)
  // This handles embedded app requests from Shopify admin
  app.get('*', (req, res, next) => {
    // Skip API routes
    if (req.path.startsWith('/api')) {
      return next();
    }
    
    // Log request for debugging
    console.log(`[Frontend Request] ${req.method} ${req.path}`, {
      query: req.query,
      headers: {
        'user-agent': req.headers['user-agent'],
        'referer': req.headers['referer'],
      }
    });
    
    try {
      const indexPath = path.join(frontendDistPath, 'index.html');
      
      if (!fs.existsSync(indexPath)) {
        console.error(`[Error] index.html not found at: ${indexPath}`);
        return res.status(500).send('Frontend not built. Please run: npm run build');
      }
      
      let html = fs.readFileSync(indexPath, 'utf8');
      
      // Inject Shopify API key as a script tag before closing </head>
      const apiKey = process.env.SHOPIFY_API_KEY || '';
      const scriptTag = `<script>window.__SHOPIFY_API_KEY__ = '${apiKey}';</script>`;
      
      // Insert before closing </head> tag (only if not already present)
      if (!html.includes('__SHOPIFY_API_KEY__')) {
        html = html.replace('</head>', `${scriptTag}</head>`);
      }
      
      res.setHeader('Content-Type', 'text/html');
      res.send(html);
    } catch (error) {
      console.error('[Error] Failed to serve index.html:', error);
      res.status(500).send('Failed to load application');
    }
  });
}

// Initialize services
async function startServer() {
  try {
    // Initialize database
    await initializeDatabase();
    console.log('✅ Database connected');

    // Initialize Redis
    await initializeRedis();
    console.log('✅ Redis connected');

    // Initialize queues
    await initializeQueue();
    console.log('✅ Queue system initialized');

    // Start server
    app.listen(PORT, () => {
      console.log(`🚀 Server running on port ${PORT}`);
      console.log(`📊 Report Pro API ready`);
    });
  } catch (error) {
    console.error('❌ Failed to start server:', error);
    process.exit(1);
  }
}

startServer();

export default app;

