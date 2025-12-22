import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import path from 'path';
import { shopifyApi, LATEST_API_VERSION } from '@shopify/shopify-api';
import '@shopify/shopify-api/adapters/node';

import { setupRoutes } from './routes';
import { initializeDatabase } from './database/connection';
import { initializeRedis } from './services/redis';
import { initializeQueue } from './services/queue';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Initialize Shopify API
const shopify = shopifyApi({
  apiKey: process.env.SHOPIFY_API_KEY!,
  apiSecretKey: process.env.SHOPIFY_API_SECRET!,
  scopes: process.env.SHOPIFY_SCOPES?.split(',') || [],
  hostName: process.env.SHOPIFY_APP_URL?.replace(/https?:\/\//, '') || 'localhost:3000',
  apiVersion: LATEST_API_VERSION,
  isEmbeddedApp: true,
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
  res.setHeader(
    'Content-Security-Policy',
    "frame-ancestors 'self' https://*.myshopify.com https://admin.shopify.com;"
  );
  next();
});

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Setup API routes
setupRoutes(app, shopify);

// Serve static files from frontend/dist in production
if (process.env.NODE_ENV === 'production') {
  const frontendDistPath = path.join(__dirname, '..', 'frontend', 'dist');
  app.use(express.static(frontendDistPath));
  
  // Serve index.html for all non-API routes (SPA routing)
  // Inject Shopify API key for App Bridge
  app.get('*', (req, res) => {
    if (!req.path.startsWith('/api')) {
      const indexPath = path.join(frontendDistPath, 'index.html');
      const fs = require('fs');
      let html = fs.readFileSync(indexPath, 'utf8');
      
      // Inject Shopify API key as a script tag before closing </head>
      const apiKey = process.env.SHOPIFY_API_KEY || '';
      const scriptTag = `<script>window.__SHOPIFY_API_KEY__ = '${apiKey}';</script>`;
      
      // Insert before closing </head> tag
      html = html.replace('</head>', `${scriptTag}</head>`);
      
      res.send(html);
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

