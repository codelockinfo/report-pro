# Live Server Setup Guide - Hostinger

## ✅ Build Completed Successfully!

Your application has been built successfully. Now let's get it running on your live server.

## 📋 Step-by-Step Setup

### Step 1: Update .env File on Server

SSH into your Hostinger server and update the `.env` file with your database name:

```bash
# SSH into Hostinger
ssh your-username@your-hostinger-ip

# Navigate to your project directory
cd public_html
# OR if using subdomain:
cd subdomains/your-subdomain/public_html

# Edit .env file
nano .env
```

**Update these values in your `.env` file:**

```env
# Server Configuration
PORT=3000
NODE_ENV=production

# Shopify App Configuration
SHOPIFY_API_KEY=your_shopify_api_key
SHOPIFY_API_SECRET=your_shopify_api_secret
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
SHOPIFY_APP_URL=https://your-domain.com

# MySQL Database Configuration (IMPORTANT: Update database name!)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u402017191_report_pro
DB_USER=u402017191_report_pro
DB_PASSWORD=your_database_password

# Redis Configuration (Optional)
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=

# JWT Secret
JWT_SECRET=your_very_strong_random_secret_key_here

# Email Configuration
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USER=tailorpro@happyeventsurat.com
SMTP_PASS=Tailor@99
SMTP_FROM_EMAIL=tailorpro@happyeventsurat.com
SMTP_FROM_NAME=TailorPro

# App Settings
APP_NAME=Better Reports
APP_URL=https://your-domain.com
```

**Important:** Make sure `DB_NAME=u402017191_report_pro` matches your Hostinger database name.

### Step 2: Verify Build Files Exist

Check that build files are present:

```bash
# Check backend build
ls -la dist/

# Check frontend build
ls -la frontend/dist/
```

You should see:
- `dist/server.js` (backend)
- `frontend/dist/index.html` (frontend)
- `frontend/dist/assets/` (frontend assets)

### Step 3: Install PM2 (Process Manager)

PM2 will keep your app running 24/7:

```bash
# Install PM2 globally
npm install -g pm2

# Verify installation
pm2 --version
```

### Step 4: Start Your Application

Start the app using PM2:

```bash
# Start the app
pm2 start dist/server.js --name report-pro

# Check status
pm2 list

# View logs
pm2 logs report-pro

# Save PM2 configuration
pm2 save

# Set PM2 to start on server reboot
pm2 startup
```

**Expected Output:**
```
✅ Database connected
✅ Redis connected (or warning if not available)
✅ Queue system initialized
🚀 Server running on port 3000
📊 Report Pro API ready
```

### Step 5: Configure Web Server

#### Option A: Using Hostinger's Auto-Deploy (Recommended)

If Hostinger auto-deployed your app, it should already be configured. Just verify:

1. Check if your domain is pointing to the correct directory
2. Verify the app is accessible at `https://your-domain.com`

#### Option B: Manual Nginx Configuration

If you need to configure Nginx manually:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # SSL certificates (Hostinger usually handles this)
    # ssl_certificate /path/to/cert.pem;
    # ssl_certificate_key /path/to/key.pem;

    # Frontend static files
    location / {
        root /home/u402017191/domains/your-domain.com/public_html/frontend/dist;
        try_files $uri $uri/ /index.html;
    }

    # Backend API
    location /api {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Health check
    location /health {
        proxy_pass http://localhost:3000;
    }
}
```

#### Option C: Using .htaccess (Apache)

Create `.htaccess` in `public_html`:

```apache
# Rewrite API requests to Node.js server
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

### Step 6: Test Your Application

1. **Test Health Endpoint:**
   ```bash
   curl http://localhost:3000/health
   ```
   Should return: `{"status":"ok","timestamp":"..."}`

2. **Test Frontend:**
   Visit: `https://your-domain.com`
   Should show your React app

3. **Test API:**
   Visit: `https://your-domain.com/api/health`
   Should return: `{"status":"ok","timestamp":"..."}`

### Step 7: Update Shopify App Settings

Go to Shopify Partners Dashboard → Your App → App setup:

1. **App URL:**
   ```
   https://your-domain.com
   ```

2. **Allowed redirection URL(s):**
   ```
   https://your-domain.com/api/auth/shopify/callback
   ```

3. **Required Scopes:**
   ```
   read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
   ```

4. **Save** all settings

### Step 8: Test Shopify OAuth Flow

Visit this URL (replace with your domain and test store):
```
https://your-domain.com/api/auth/shopify?shop=your-test-store.myshopify.com
```

You should be redirected to Shopify for authorization, then back to your app.

## 🔧 Troubleshooting

### App Won't Start

```bash
# Check PM2 logs
pm2 logs report-pro

# Check if port 3000 is in use
lsof -i :3000

# Restart the app
pm2 restart report-pro
```

### Database Connection Error

```bash
# Verify database credentials in .env
cat .env | grep DB_

# Test database connection
mysql -u u402017191_report_pro -p u402017191_report_pro
```

### Frontend Not Loading

```bash
# Check if frontend/dist exists
ls -la frontend/dist/

# Rebuild if needed
cd frontend && npm run build && cd ..
```

### API Not Working

```bash
# Check if server is running
pm2 list

# Check server logs
pm2 logs report-pro

# Test API directly
curl http://localhost:3000/api/health
```

## 📝 Useful Commands

```bash
# PM2 Commands
pm2 list                    # View all apps
pm2 logs report-pro         # View logs
pm2 restart report-pro      # Restart app
pm2 stop report-pro         # Stop app
pm2 delete report-pro       # Remove app

# View logs in real-time
pm2 logs report-pro --lines 100

# Monitor app
pm2 monit
```

## ✅ Success Checklist

- [ ] `.env` file updated with correct database name (`u402017191_report_pro`)
- [ ] PM2 installed and app started
- [ ] Server running on port 3000
- [ ] Frontend accessible at `https://your-domain.com`
- [ ] API accessible at `https://your-domain.com/api/health`
- [ ] Shopify app settings updated
- [ ] OAuth flow tested successfully

## 🎉 You're Done!

Your app should now be live and accessible. If you encounter any issues, check the PM2 logs and verify all environment variables are correct.

