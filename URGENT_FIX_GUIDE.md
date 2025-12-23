# 🚨 URGENT FIX - 3 Critical Issues

You have 3 issues that need immediate fixing:

## Issue 1: Database is Empty ❌

Your database has **no tables**. The migrations should run automatically, but they haven't.

### Fix: Create Tables Manually

1. **Open phpMyAdmin** (you're already there)
2. **Select your database**: `u402017191_report_pro`
3. **Go to SQL tab**
4. **Copy and paste** the entire content from `database_setup.sql` file
5. **Click "Go"**

This will create all required tables including:
- ✅ `shops` - Store shop data
- ✅ `shopify_sessions` - **CRITICAL for embedded apps** (fixes 403 error)
- ✅ `reports`, `schedules`, `settings`, `integrations`, `charts`

---

## Issue 2: 404 Error on OAuth Callback ❌

The URL `/api/auth/shopify/callback` returns 404, which means:
- Node.js server is NOT running, OR
- Web server (Apache) is not routing requests to Node.js

### Fix: Start Node.js Server

**Step 1: SSH into your Hostinger server**

```bash
ssh your-username@your-hostinger-ip
```

**Step 2: Navigate to your app directory**

```bash
cd public_html
# OR if using subdomain:
cd subdomains/your-subdomain/public_html
```

**Step 3: Check if Node.js server is running**

```bash
pm2 list
```

If you see `report-pro` in the list and it shows "online", the server is running.
If not, continue to Step 4.

**Step 4: Start the server**

```bash
# Make sure you're in the project directory
cd /path/to/public_html

# Start with PM2
pm2 start dist/server.js --name report-pro

# Or use ecosystem config
pm2 start ecosystem.config.js

# Check status
pm2 list

# View logs to check for errors
pm2 logs report-pro --lines 50
```

**Expected logs:**
```
✅ Database connected
✅ Redis connected (or warning if Redis not available)
✅ Queue system initialized
🚀 Server running on port 3000
📊 Report Pro API ready
```

**Step 5: Save PM2 configuration**

```bash
pm2 save
pm2 startup  # This ensures server starts on reboot
```

---

## Issue 3: Web Server Configuration ❌

Apache needs to proxy requests to Node.js. Since files are in `public_html`, you need `.htaccess`.

### Fix: Create .htaccess File

**Step 1: Create `.htaccess` file in `public_html` directory**

I've created a `.htaccess` file for you. Upload it to your `public_html` directory.

**Step 2: Verify mod_rewrite and mod_proxy are enabled**

On Hostinger, these modules are usually enabled. If not, contact Hostinger support.

**Step 3: Test the configuration**

After creating `.htaccess` and starting Node.js:

```bash
# Test health endpoint
curl http://localhost:3000/health

# Should return: {"status":"ok","timestamp":"..."}
```

---

## Complete Setup Steps (Do in Order)

### Step 1: Create Database Tables ✅

1. Open phpMyAdmin
2. Select database: `u402017191_report_pro`
3. Go to SQL tab
4. Copy/paste content from `database_setup.sql`
5. Click "Go"
6. Verify tables were created (should see 7 tables)

### Step 2: Verify .env File

Check your `.env` file has correct values:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u402017191_report_pro
DB_USER=u402017191_report_pro
DB_PASSWORD=your_password

SHOPIFY_API_KEY=your_key
SHOPIFY_API_SECRET=your_secret
SHOPIFY_APP_URL=https://goldenrod-fox-366864.hostingersite.com
NODE_ENV=production
PORT=3000
```

### Step 3: Upload .htaccess File

1. Upload `.htaccess` file to `public_html` directory
2. Make sure it's in the root of `public_html`

### Step 4: Start Node.js Server

```bash
# SSH into server
cd public_html

# Check if PM2 is installed
pm2 --version

# If not installed:
npm install -g pm2

# Start server
pm2 start dist/server.js --name report-pro

# Check logs
pm2 logs report-pro
```

### Step 5: Verify Everything Works

```bash
# Test health endpoint (from server)
curl http://localhost:3000/health

# Test from browser
https://goldenrod-fox-366864.hostingersite.com/api/diagnostic
```

---

## Verification Checklist

After completing all steps:

- [ ] Database has 7 tables (check phpMyAdmin)
- [ ] Node.js server is running (check `pm2 list`)
- [ ] Health endpoint works: `/api/diagnostic`
- [ ] `.htaccess` file exists in `public_html`
- [ ] Server logs show: "Database connected" and "Server running on port 3000"

---

## If Still Getting 404

**Check these:**

1. **Is Node.js server running?**
   ```bash
   pm2 list
   pm2 logs report-pro
   ```

2. **Is port 3000 accessible?**
   ```bash
   curl http://localhost:3000/health
   ```

3. **Check Apache error logs:**
   ```bash
   tail -f /var/log/apache2/error.log
   # or
   tail -f /var/log/httpd/error_log
   ```

4. **Verify .htaccess is working:**
   - Make sure `.htaccess` file is in `public_html` root
   - Check file permissions (should be readable)
   - Verify mod_rewrite is enabled

5. **Check Hostinger control panel:**
   - Make sure Node.js is enabled
   - Check if there are any port restrictions
   - Verify domain is pointing to correct directory

---

## Quick Test After Fix

Once all 3 issues are fixed:

1. **Uninstall app** from Shopify (if already installed)
2. **Install app again** through Partner Dashboard
3. **Check database** - should see shop record in `shops` table
4. **Check database** - should see session record in `shopify_sessions` table
5. **App should load** without 403 error!

---

## Need Help?

If you still get errors, share:

1. Output of `pm2 list`
2. Output of `pm2 logs report-pro --lines 50`
3. Screenshot of phpMyAdmin showing tables
4. Output of `curl http://localhost:3000/health`

This will help identify what's still broken.

