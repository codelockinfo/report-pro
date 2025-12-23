# Final Troubleshooting Steps for 403 Error

## Your Configuration Status ✅

Your Partner Dashboard is correctly configured:
- App URL: ✅ `https://goldenrod-fox-366864.hostingersite.com`
- Redirect URL: ✅ `https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify/callback`
- Embedded app: ✅ Enabled

## Most Likely Issue: Session Storage Table Missing

The `shopify_sessions` table is **critical** for embedded apps. If it doesn't exist, you'll get 403 errors.

### Step 1: Verify Table Exists

Run this SQL query:

```sql
SHOW TABLES LIKE 'shopify_sessions';
```

**If the table doesn't exist**, create it:

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 2: Verify Environment Variables

On your server, check `.env`:

```bash
cat .env | grep SHOPIFY
```

Must show:
```
SHOPIFY_API_KEY=your_actual_key
SHOPIFY_API_SECRET=your_actual_secret
SHOPIFY_APP_URL=https://goldenrod-fox-366864.hostingersite.com
```

**CRITICAL:** `SHOPIFY_APP_URL` must:
- ✅ Start with `https://`
- ✅ Match Partner Dashboard exactly
- ✅ NO trailing slash
- ✅ NO `/` at the end

### Step 3: Restart Server After Changes

```bash
pm2 restart report-pro
# or
pm2 reload report-pro
```

### Step 4: Test Installation Flow

1. **Uninstall app** from Shopify admin (if already installed)
2. **Clear browser cache/cookies**
3. **Install app again** through Partner Dashboard
4. **Watch server logs** during installation:
   ```bash
   pm2 logs report-pro --lines 50
   ```

5. **Immediately check database** after OAuth completes:
   ```sql
   -- Should show your shop with access_token
   SELECT shop_domain, access_token IS NOT NULL as has_token 
   FROM shops 
   ORDER BY created_at DESC 
   LIMIT 1;
   
   -- Should show session record
   SELECT id, shop, created_at 
   FROM shopify_sessions 
   ORDER BY created_at DESC 
   LIMIT 1;
   ```

### Step 5: Check Server Logs

After installation attempt, look for:

✅ **Success indicators:**
```
[OAUTH_CALLBACK] Session stored successfully for shop: your-shop.myshopify.com
[OAUTH_CALLBACK] Shop data saved successfully: your-shop.myshopify.com
```

❌ **Error indicators:**
```
[OAUTH_CALLBACK] Failed to store session
[OAUTH_CALLBACK] No session in callback response
Error storing session: ...
```

### Step 6: Test Diagnostic Endpoint

```bash
curl https://goldenrod-fox-366864.hostingersite.com/api/diagnostic
```

Should return JSON. If you get 403 here, the issue is with routing or server configuration.

## If Still Getting 403

### Debug Information to Collect

1. **Server Logs** (last 100 lines):
   ```bash
   pm2 logs report-pro --lines 100 > server-logs.txt
   ```

2. **Database Check**:
   ```sql
   -- Copy results of these queries
   SELECT COUNT(*) as shops_count FROM shops;
   SELECT COUNT(*) as sessions_count FROM shopify_sessions;
   SELECT shop_domain, created_at FROM shops ORDER BY created_at DESC LIMIT 5;
   ```

3. **Environment Check**:
   ```bash
   # On server
   echo $SHOPIFY_APP_URL
   cat .env | grep SHOPIFY_APP_URL
   ```

4. **Browser Network Tab**:
   - Open DevTools (F12)
   - Go to Network tab
   - Try to access app
   - Find 403 request
   - Screenshot the request/response

### Common Fixes

**Fix 1: Session table doesn't exist**
```sql
-- Run the CREATE TABLE statement above
```

**Fix 2: Environment variable wrong**
```bash
# Edit .env file
nano .env
# Fix SHOPIFY_APP_URL (no trailing slash!)
# Save and restart
pm2 restart report-pro
```

**Fix 3: Database connection issue**
```sql
-- Test connection with your credentials
mysql -u u402017191_report_pro -p -h your_host report_pro
```

**Fix 4: Frontend not built**
```bash
cd /path/to/app
npm run build
pm2 restart report-pro
```

## Expected Flow After Fix

1. User clicks "Install" in Shopify
2. Redirects to OAuth authorization
3. User approves permissions
4. OAuth callback stores session in `shopify_sessions` table
5. OAuth callback saves shop in `shops` table
6. Redirects to embedded app URL
7. App loads successfully (no 403!)

## Still Not Working?

Share these with me:
1. ✅ Server logs showing OAuth callback
2. ✅ Database query results (shops and shopify_sessions)
3. ✅ `.env` file SHOPIFY_APP_URL value (mask secret)
4. ✅ Screenshot of browser network tab showing 403 request

This will help pinpoint the exact issue!

