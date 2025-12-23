# Verification Checklist - Partner Dashboard Configuration

## ✅ Your Partner Dashboard Settings (Verified)

Based on your screenshot, these are correctly configured:

- **App URL**: `https://goldenrod-fox-366864.hostingersite.com` ✅
- **Redirect URL**: `https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify/callback` ✅
- **Embed app in Shopify admin**: `true` ✅
- **Preferences URL**: `https://goldenrod-fox-366864.hostingersite.com/settings` ✅
- **Scopes**: `read_customers, read_inventory, read_locations, read_orders, read_products, read_translations, customer_read_orders` ✅

## 🔍 Verification Steps

### 1. Verify .env File Matches

On your server, check that your `.env` file has:

```env
SHOPIFY_API_KEY=your_api_key_from_partner_dashboard
SHOPIFY_API_SECRET=your_api_secret_from_partner_dashboard
SHOPIFY_APP_URL=https://goldenrod-fox-366864.hostingersite.com
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
NODE_ENV=production
PORT=3000
```

**Critical:** `SHOPIFY_APP_URL` must match exactly:
- ✅ Correct: `https://goldenrod-fox-366864.hostingersite.com`
- ❌ Wrong: `https://goldenrod-fox-366864.hostingersite.com/` (trailing slash)
- ❌ Wrong: `http://goldenrod-fox-366864.hostingersite.com` (HTTP instead of HTTPS)

### 2. Verify Database Tables Exist

Connect to your MySQL database and run:

```sql
-- Check shops table
SHOW TABLES LIKE 'shops';
DESCRIBE shops;

-- Check shopify_sessions table (CRITICAL for embedded apps)
SHOW TABLES LIKE 'shopify_sessions';
DESCRIBE shopify_sessions;
```

**If `shopify_sessions` table doesn't exist**, create it:

```sql
CREATE TABLE IF NOT EXISTS shopify_sessions (
  id VARCHAR(255) PRIMARY KEY,
  shop VARCHAR(255) NOT NULL,
  state VARCHAR(255),
  is_online TINYINT(1) DEFAULT 0,
  scope VARCHAR(255),
  expires DATETIME,
  access_token TEXT,
  user_id VARCHAR(255),
  session_data JSON,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_shop (shop),
  INDEX idx_expires (expires)
);
```

### 3. Test After Installation

After installing the app, check the database:

```sql
-- Check if shop was saved
SELECT shop_domain, store_name, 
       access_token IS NOT NULL as has_token,
       created_at 
FROM shops 
WHERE shop_domain LIKE '%your-shop%';

-- Check if session was stored
SELECT id, shop, is_online, expires, created_at 
FROM shopify_sessions 
WHERE shop LIKE '%your-shop%'
ORDER BY created_at DESC;
```

**Expected Results:**
- ✅ Shop record exists with `has_token = 1`
- ✅ Session record exists in `shopify_sessions` table

### 4. Check Server Logs

After attempting to access the app, check logs:

```bash
pm2 logs report-pro --lines 100
```

Look for these log messages:
- `[OAUTH_CALLBACK] Session stored successfully` - Confirms session was saved
- `[INSTALL] Shop is installed, allowing request` - Confirms install router working
- `[EMBEDDED_AUTH] Shop is installed, allowing request` - Confirms embedded auth working
- `[Frontend Request] GET /` - Confirms frontend is being served

### 5. Test Diagnostic Endpoint

```bash
curl https://goldenrod-fox-366864.hostingersite.com/api/diagnostic
```

Should return JSON with diagnostic info.

### 6. Common Issues & Fixes

#### Issue: 403 Error After Installation

**Possible Causes:**

1. **Session not stored** (MOST COMMON)
   - Check `shopify_sessions` table after installation
   - Verify session exists for your shop
   - Fix: Ensure `shopify_sessions` table exists and session storage works

2. **Shop not in database**
   - Check `shops` table after installation
   - Verify shop exists with access_token
   - Fix: Re-run OAuth installation flow

3. **Environment variable mismatch**
   - `SHOPIFY_APP_URL` doesn't match Partner Dashboard
   - Fix: Update `.env` file and restart server

4. **Route conflict**
   - Install router or embedded auth middleware blocking
   - Fix: Check server logs for which middleware is hit

5. **Database connection issue**
   - Session storage can't connect to database
   - Fix: Verify database credentials in `.env`

#### Quick Test Commands

```bash
# Test database connection
mysql -u u402017191_report_pro -p -h your_db_host report_pro -e "SELECT 1;"

# Check if server is running
curl http://localhost:3000/health

# Check if frontend is built
ls -la frontend/dist/index.html

# Restart server (after .env changes)
pm2 restart report-pro
```

## 🚀 Deployment Checklist

Before testing installation:

- [ ] `.env` file has correct `SHOPIFY_APP_URL` (no trailing slash)
- [ ] `.env` file has correct `SHOPIFY_API_KEY` and `SHOPIFY_API_SECRET`
- [ ] Database tables exist (`shops` and `shopify_sessions`)
- [ ] Server is running and accessible
- [ ] Frontend is built (`frontend/dist/` exists)
- [ ] Partner Dashboard URLs match `.env` exactly
- [ ] Server restarted after any `.env` changes

## 📝 Next Steps

1. **Verify `.env` file matches Partner Dashboard**
2. **Ensure `shopify_sessions` table exists**
3. **Restart server**: `pm2 restart report-pro`
4. **Try installing app again**
5. **Check database immediately after installation**
6. **Review server logs for errors**

If 403 persists, share:
- Server logs (last 50 lines)
- Database query results for your shop
- `.env` file values (mask secrets)

