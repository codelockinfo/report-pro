# Hostinger Deployment Guide

Complete guide to deploy your Shopify Reports App to Hostinger.

## 📋 Pre-Deployment Checklist

- [ ] Code is complete and tested
- [ ] All dependencies are in `package.json`
- [ ] `.env` file is ready (will create on server)
- [ ] Database credentials ready
- [ ] Redis access ready (or use Hostinger Redis)
- [ ] Shopify app credentials ready
- [ ] Git repository is set up

## 🚀 Deployment Steps

### Step 1: Prepare Your Code

1. **Make sure all code is committed:**
```bash
git add .
git commit -m "Ready for Hostinger deployment"
git push origin main
```

2. **Verify these files exist:**
   - ✅ `package.json` - All dependencies listed
   - ✅ `.env.example` - Template for environment variables
   - ✅ `README.md` - Documentation
   - ✅ All source files in `src/` and `frontend/`

### Step 2: Access Hostinger

1. Login to Hostinger control panel
2. Go to your hosting account
3. Access **File Manager** or use **SSH** (recommended)

### Step 3: Clone Your Repository

**Via SSH (Recommended):**
```bash
# Connect via SSH
ssh your-username@your-hostinger-ip

# Navigate to your domain directory
cd public_html
# OR if using subdomain:
cd subdomains/your-subdomain/public_html

# Clone your repository
git clone https://github.com/your-username/report-pro.git .

# OR if you already have files, pull latest:
git pull origin main
```

**Via File Manager:**
1. Upload your files via File Manager
2. Extract if needed

### Step 4: Install Dependencies

```bash
# Install backend dependencies (include devDependencies for building)
npm install

# Install frontend dependencies (required for building)
cd frontend
npm install
cd ..
```

**Important:** Use `npm install` (not `--production`) because we need devDependencies like TypeScript and Vite to build the application.

### Step 5: Build the Application

```bash
# Build both backend and frontend
# The prebuild script will automatically install frontend deps if needed
npm run build
```

This will:
- Compile TypeScript to JavaScript (`dist/` folder)
- Build React frontend (`frontend/dist/` folder)

### Step 6: Set Up Environment Variables

Create `.env` file on server:

```bash
# Create .env file
nano .env
# OR use File Manager to create .env file
```

**Copy this template and fill in your values:**

```env
# Server Configuration
PORT=3000
NODE_ENV=production

# Shopify App Configuration
SHOPIFY_API_KEY=your_shopify_api_key
SHOPIFY_API_SECRET=your_shopify_api_secret
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
SHOPIFY_APP_URL=https://your-domain.com

# MySQL Database Configuration (Hostinger MySQL)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password

# Redis Configuration (if available on Hostinger)
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=

# JWT Secret (generate a strong random string)
JWT_SECRET=your_very_strong_random_secret_key_here

# Email Configuration (for scheduled reports)
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=your_email@your-domain.com
SMTP_PASS=your_email_password

# App Settings
APP_NAME=Better Reports
APP_URL=https://your-domain.com
```

**Important:**
- Replace `your-domain.com` with your actual Hostinger domain
- Get MySQL credentials from Hostinger control panel
- Generate a strong JWT_SECRET (use: `openssl rand -base64 32`)

### Step 7: Set Up Database

1. **Create MySQL Database:**
   - Go to Hostinger control panel
   - Find "MySQL Databases"
   - Create new database: `report_pro`
   - Create database user
   - Note down credentials

2. **Database will auto-migrate:**
   - Tables are created automatically on first server start
   - Or run manually: `npm run migrate`

### Step 8: Set Up Redis (Optional but Recommended)

**Option A: Use Hostinger Redis (if available)**
- Get Redis credentials from Hostinger
- Update `.env` with Redis details

**Option B: Skip Redis (for now)**
- App will work without Redis
- Caching and queues won't work, but core features will

### Step 9: Set Up Process Manager (PM2)

Install PM2 to keep your app running:

```bash
# Install PM2 globally
npm install -g pm2

# Start your app with PM2
pm2 start dist/server.js --name report-pro

# Save PM2 configuration
pm2 save

# Set PM2 to start on server reboot
pm2 startup
```

**PM2 Commands:**
```bash
pm2 list          # View running apps
pm2 logs report-pro  # View logs
pm2 restart report-pro  # Restart app
pm2 stop report-pro    # Stop app
```

### Step 10: Configure Web Server (Nginx/Apache)

**If using Nginx:**

Create/edit Nginx configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    # Frontend static files
    location / {
        root /path/to/report-pro/frontend/dist;
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

**If using Apache (.htaccess):**

Create `.htaccess` in `public_html`:

```apache
RewriteEngine On
RewriteBase /

# API routes
RewriteRule ^api/(.*)$ http://localhost:3000/api/$1 [P,L]

# Frontend routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.html [L]
```

### Step 11: Update Shopify App Settings

1. Go to Shopify Partner Dashboard
2. Open your app settings
3. Update:

   **App URL:**
   ```
   https://your-domain.com
   ```

   **Allowed redirection URL(s):**
   ```
   https://your-domain.com/api/auth/shopify/callback
   ```

   **Required Scopes:**
   ```
   read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
   ```

4. **Save settings**

### Step 12: Test Your Deployment

1. **Check health endpoint:**
   ```
   https://your-domain.com/health
   ```
   Should return: `{"status":"ok","timestamp":"..."}`

2. **Test OAuth installation:**
   ```
   https://your-domain.com/api/auth/shopify?shop=your-test-store.myshopify.com
   ```

3. **Check logs:**
   ```bash
   pm2 logs report-pro
   ```

## 🔧 Hostinger-Specific Notes

### Node.js Version
- Hostinger usually provides Node.js 18+
- Check version: `node --version`
- If needed, request Node.js upgrade from Hostinger support

### Port Configuration
- Hostinger may require specific ports
- Check with Hostinger support for allowed ports
- Default: Port 3000 (may need to change)

### SSL Certificate
- Hostinger provides free SSL certificates
- Enable SSL in Hostinger control panel
- Update `.env`: `SHOPIFY_APP_URL=https://your-domain.com`

### Database Access
- MySQL is usually available on `localhost`
- Get credentials from Hostinger control panel
- Database name format: `username_dbname`

## 📝 Post-Deployment Checklist

- [ ] App is running (check `/health`)
- [ ] Database connection works
- [ ] Redis connection works (if configured)
- [ ] OAuth flow works (test installation)
- [ ] Frontend loads correctly
- [ ] API endpoints respond
- [ ] PM2 is running and auto-starts
- [ ] SSL certificate is active
- [ ] Shopify app settings updated

## 🐛 Troubleshooting

### App won't start
```bash
# Check logs
pm2 logs report-pro

# Check if port is in use
netstat -tulpn | grep 3000

# Check Node.js version
node --version
```

### Database connection error
- Verify MySQL credentials in `.env`
- Check database exists in Hostinger
- Test connection: `mysql -u user -p database_name`

### 502 Bad Gateway
- Check if app is running: `pm2 list`
- Check Nginx/Apache configuration
- Verify proxy_pass URL is correct

### OAuth not working
- Verify `SHOPIFY_APP_URL` in `.env` matches your domain
- Check Shopify app settings match exactly
- Ensure HTTPS is enabled

## 🔄 Updating Your App

```bash
# Pull latest code
git pull origin main

# Install new dependencies (if any)
npm install --production
cd frontend && npm install --production && cd ..

# Rebuild
npm run build

# Restart app
pm2 restart report-pro
```

## 📞 Support

If you encounter issues:
1. Check PM2 logs: `pm2 logs report-pro`
2. Check server logs in Hostinger
3. Verify all environment variables
4. Contact Hostinger support if needed

---

**Your app is now live on Hostinger! 🎉**

