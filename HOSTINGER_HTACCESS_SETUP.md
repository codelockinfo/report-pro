# Hostinger .htaccess Configuration - Complete ✅

## What Was Done

I've created a production-ready `.htaccess` file in the `dist/` folder that will properly link your frontend and backend on Hostinger.

## File Location

The `.htaccess` file is located at: **`dist/.htaccess`**

When you upload the `dist/` folder contents to Hostinger's `public_html/`, this `.htaccess` file will automatically be included.

## How It Works

### Configuration
The `.htaccess` file proxies **ALL requests** to your Node.js server running on port 3000:

```apache
RewriteRule ^(.*)$ http://localhost:3000/$1 [P,L]
```

This means:
- **Frontend requests** → Proxied to Node.js → Node.js serves React app
- **API requests** (`/api/*`) → Proxied to Node.js → Node.js handles API routes
- **All requests** → Handled by your Node.js server

### Why This Approach?

Your Node.js server (`server/src/server.ts`) is already configured to:
1. Handle API routes (`/api/*`)
2. Serve frontend static files from `dist/web/`
3. Serve `index.html` for SPA routing

So proxying everything to Node.js is the simplest and most reliable approach.

## Setup Steps on Hostinger

1. **Upload dist/ folder contents to public_html/**
   - Upload all files from your local `dist/` folder to `public_html/` on Hostinger

2. **Start Node.js server:**
   ```bash
   cd ~/public_html
   npm install --production
   pm2 start server/server.js --name "shopify-app"
   pm2 save
   ```

3. **Configure environment variables:**
   - Create `.env` file in `public_html/` with your Shopify credentials

4. **Verify port:**
   - The `.htaccess` assumes Node.js runs on port **3000**
   - If Hostinger assigns a different port, update:
     - `.env` file: `PORT=your_port`
     - `.htaccess` file: Replace `3000` with your port

## Additional Features Included

The `.htaccess` file also includes:

✅ **Security Headers**
- X-Frame-Options (clickjacking protection)
- X-XSS-Protection
- X-Content-Type-Options
- Referrer-Policy

✅ **Performance Optimizations**
- Gzip compression for text files
- Cache headers for static assets (CSS, JS, images)

## Testing After Deployment

1. **Frontend:** Visit `https://your-domain.com`
   - Should load your React app

2. **API:** Visit `https://your-domain.com/api/health` (if you have a health endpoint)
   - Should return API response

3. **Shopify OAuth:** Visit `https://your-domain.com?shop=your-shop.myshopify.com`
   - Should redirect to Shopify OAuth flow

## Troubleshooting

### 502 Bad Gateway
- **Cause:** Node.js server not running
- **Fix:** Start the server with PM2 or check if it's running

### Wrong Port Error
- **Cause:** Node.js running on different port than 3000
- **Fix:** Update `.htaccess` to match your port, or set `PORT=3000` in `.env`

### Proxy Module Not Enabled
- **Cause:** Apache proxy module not enabled
- **Fix:** Contact Hostinger support to enable `mod_proxy` and `mod_proxy_http`

## Alternative Configuration

If you prefer Apache to serve static files directly (better performance for large assets), you can modify the `.htaccess` to only proxy API requests:

```apache
# Only proxy API requests
RewriteRule ^api/(.*)$ http://localhost:3000/api/$1 [P,L]

# Serve frontend directly
RewriteCond %{REQUEST_URI} !^/api
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /web/index.html [L]
```

However, the current configuration (proxy everything to Node.js) is recommended because:
- Your Node.js server already handles everything correctly
- Simpler configuration
- No path conflicts
- Consistent behavior

---

**The `.htaccess` file is ready for deployment!** 🚀

