# Troubleshooting 403 Forbidden Error in Shopify Embedded App

## Problem
Getting "403 Forbidden" error when accessing the app through Shopify admin:
`https://admin.shopify.com/store/cls-rakshita/apps/reportpro-easy-report`

## Common Causes & Solutions

### 1. ✅ Check Shopify App Settings

Go to **Shopify Partner Dashboard** → Your App → **App setup** → **URLs**:

**App URL must be:**
```
https://your-domain.com
```
- Must use HTTPS (not HTTP)
- Must match your actual domain exactly
- No trailing slash

**Allowed redirection URL(s):**
```
https://your-domain.com/api/auth/shopify/callback
```

**✅ Embed app in Shopify admin** - Must be CHECKED

### 2. ✅ Verify Environment Variables

On your server, check `.env` file has:
```env
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_APP_URL=https://your-domain.com
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
NODE_ENV=production
PORT=3000
```

**Important:** `SHOPIFY_APP_URL` must match your App URL in Shopify settings exactly.

### 3. ✅ Check Server Logs

SSH into your server and check logs:
```bash
pm2 logs report-pro
```

Look for:
- ✅ "Server running on port 3000"
- ✅ "Database connected"
- ❌ Any errors about missing files or failed requests

### 4. ✅ Verify Frontend is Built

Check if `frontend/dist/index.html` exists:
```bash
ls -la frontend/dist/
```

If missing, rebuild:
```bash
npm run build
pm2 restart report-pro
```

### 5. ✅ Test Direct Access

Try accessing your app directly (not through Shopify):
```
https://your-domain.com
```

Should show your app (may show error about missing shop parameter, but should NOT be 403).

### 6. ✅ Test Diagnostic Endpoint

Visit:
```
https://your-domain.com/api/diagnostic
```

Should return JSON with diagnostic information.

### 7. ✅ Check CSP Headers

The server should send these headers:
```
Content-Security-Policy: frame-ancestors 'self' https://*.myshopify.com https://admin.shopify.com https://*.admin.shopify.com;
```

Verify with:
```bash
curl -I https://your-domain.com
```

### 8. ✅ Verify App is Running

Check if Node.js app is running:
```bash
pm2 list
pm2 status report-pro
```

Should show `online` status.

### 9. ✅ Check Web Server Configuration

If using Apache (`.htaccess`), ensure it's configured correctly:
```apache
RewriteEngine On

# Proxy API requests to Node.js
RewriteCond %{REQUEST_URI} ^/api [NC]
RewriteRule ^api/(.*)$ http://localhost:3000/api/$1 [P,L]

# Serve frontend files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/api
RewriteRule ^(.*)$ /frontend/dist/index.html [L]
```

### 10. ✅ Reinstall the App

If all else fails, try reinstalling:

1. Go to Shopify Admin → Apps → Your App
2. Click "Uninstall" (if installed)
3. Go to Shopify Partner Dashboard → Your App
4. Click "Install app" or use test link
5. Complete OAuth flow

## Quick Checklist

- [ ] App URL in Shopify matches your domain exactly
- [ ] "Embed app in Shopify admin" is checked
- [ ] `.env` file has correct `SHOPIFY_APP_URL`
- [ ] Frontend is built (`frontend/dist/index.html` exists)
- [ ] Server is running (`pm2 list` shows online)
- [ ] Direct access works (https://your-domain.com)
- [ ] Diagnostic endpoint works (https://your-domain.com/api/diagnostic)
- [ ] CSP headers are set correctly

## Still Not Working?

1. **Check browser console** (F12) for errors
2. **Check network tab** - see what request is failing
3. **Check server logs** - `pm2 logs report-pro --lines 100`
4. **Verify domain SSL** - Must be valid HTTPS certificate

## Common Mistakes

❌ **Wrong App URL format:**
- `http://your-domain.com` (must be HTTPS)
- `https://your-domain.com/` (no trailing slash)
- `https://your-domain.com/app` (should be root)

❌ **Missing environment variables:**
- `SHOPIFY_API_KEY` not set
- `SHOPIFY_APP_URL` doesn't match Shopify settings

❌ **Frontend not built:**
- `frontend/dist/` folder missing or empty
- Need to run `npm run build`

❌ **Server not running:**
- PM2 not started
- Wrong port
- Process crashed

