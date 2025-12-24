# Hostinger Deployment Guide

## Folder Structure on Server

When you deploy the `dist/` folder to Hostinger, the structure should be:

```
public_html/
├── .htaccess          ← This file (configure as needed)
├── package.json
├── server/
│   └── server.js      ← Node.js backend
└── web/
    ├── index.html
    └── assets/        ← CSS, JS files
```

## Setup Steps

### 1. Upload dist/ folder contents to public_html

Upload all contents from your local `dist/` folder to `public_html/` on Hostinger.

### 2. Set up Node.js server

**Option A: Using PM2 (Recommended)**
```bash
cd ~/public_html
npm install --production
pm2 start server/server.js --name "shopify-app"
pm2 save
pm2 startup
```

**Option B: Using nohup**
```bash
cd ~/public_html
npm install --production
nohup node server/server.js > server.log 2>&1 &
```

**Option C: Using Hostinger Node.js Manager**
- Go to Hostinger Control Panel
- Find Node.js Manager
- Set application root to `public_html`
- Set startup file to `server/server.js`
- Set port to `3000` (or your preferred port)

### 3. Configure .htaccess

The `.htaccess` file in the `dist/` folder supports two modes:

#### Mode 1: Node.js serves everything (Recommended)
Best if your Node.js server is configured to serve the frontend.

Uncomment in `.htaccess`:
```apache
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ http://localhost:3000/$1 [P,L]
```

Comment out the API-only proxy section.

#### Mode 2: Hybrid (Apache serves frontend, Node.js serves API)
Apache serves static files, Node.js handles API requests.

Keep the default configuration:
```apache
RewriteRule ^api/(.*)$ http://localhost:3000/api/$1 [P,L]
RewriteRule ^(.*)$ /web/index.html [L]
```

### 4. Environment Variables

Create `.env` file in `public_html/`:

```env
NODE_ENV=production
PORT=3000
SHOPIFY_API_KEY=your_api_key
SHOPIFY_API_SECRET=your_api_secret
SHOPIFY_APP_URL=https://your-domain.com
SHOPIFY_SCOPES=read_products,write_products

# Database
DB_HOST=localhost
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=your_db_name

# Redis (if using)
REDIS_HOST=localhost
REDIS_PORT=6379

# Email (if using)
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=your_email@yourdomain.com
SMTP_PASS=your_password
```

### 5. Database Setup

Make sure your MySQL database has all required tables. Run the SQL from `database_setup.sql` if needed.

### 6. Verify Deployment

1. **Check Node.js server is running:**
   ```bash
   pm2 status
   # or
   curl http://localhost:3000/api/health
   ```

2. **Test frontend:**
   - Visit: `https://your-domain.com`
   - Should load the React app

3. **Test API:**
   - Visit: `https://your-domain.com/api/health`
   - Should return API response

4. **Test Shopify OAuth:**
   - Visit: `https://your-domain.com?shop=your-shop.myshopify.com`
   - Should redirect to Shopify OAuth

## Troubleshooting

### Node.js server not starting
- Check logs: `pm2 logs shopify-app`
- Verify `.env` file exists and has correct values
- Check port 3000 is available: `lsof -i :3000`

### 502 Bad Gateway
- Node.js server is not running
- Wrong port in `.htaccess`
- Proxy module not enabled in Apache

### 404 on frontend
- Check `web/index.html` exists
- Verify `.htaccess` rewrite rules
- Check file permissions

### API requests failing
- Verify Node.js server is running
- Check API routes are registered
- Verify `.htaccess` proxy rules

### Shopify OAuth errors
- Verify `SHOPIFY_API_KEY` and `SHOPIFY_API_SECRET` in `.env`
- Check `SHOPIFY_APP_URL` matches your domain
- Verify OAuth callback URL in Shopify Partner Dashboard

## Important Notes

1. **Port Configuration:** Default is port 3000. If Hostinger assigns a different port, update:
   - `.env` file: `PORT=assigned_port`
   - `.htaccess` file: Replace `3000` with assigned port
   - Server startup command

2. **SSL/HTTPS:** Hostinger should provide SSL automatically. Make sure:
   - `SHOPIFY_APP_URL` uses `https://`
   - Shopify Partner Dashboard URLs use `https://`

3. **File Permissions:**
   ```bash
   chmod 755 public_html
   chmod 644 public_html/web/index.html
   chmod 644 public_html/server/server.js
   ```

4. **Keep Server Running:**
   - Use PM2 for production
   - Set up PM2 startup script: `pm2 startup && pm2 save`

