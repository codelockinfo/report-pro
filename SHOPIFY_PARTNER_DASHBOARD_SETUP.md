# Shopify Partner Dashboard Setup for ReportPro

This guide shows you how to configure your Shopify app settings in the Partner Dashboard for your domain: **https://goldenrod-fox-366864.hostingersite.com**

## Required Settings

### 1. App URL
**URL:** `https://goldenrod-fox-366864.hostingersite.com`

**Important:**
- Must use HTTPS (not HTTP)
- Must match your actual domain exactly
- No trailing slash
- This is where Shopify will load your embedded app

### 2. Allowed redirection URL(s)
**URL:** `https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify/callback`

**Important:**
- This must match exactly (including the `/api/auth/shopify/callback` path)
- Shopify will redirect here after OAuth authorization
- Multiple URLs can be added (one per line) if needed

### 3. App Proxy
**Not required** for this app (you can leave it disabled)

### 4. Embedded App
**✅ MUST BE ENABLED**

- Check the box: "Embed app in Shopify admin"
- This allows your app to load inside the Shopify admin interface
- Required for the embedded app authentication flow

## Step-by-Step Configuration

1. **Log in to Shopify Partner Dashboard**
   - Go to https://partners.shopify.com
   - Log in with your partner account

2. **Navigate to Your App**
   - Go to "Apps" in the left sidebar
   - Click on your "ReportPro - Easy Report" app

3. **Go to App Setup**
   - Click on "App setup" tab
   - Scroll down to "URLs" section

4. **Configure App URL**
   ```
   App URL: https://goldenrod-fox-366864.hostingersite.com
   ```

5. **Configure Redirect URL**
   ```
   Allowed redirection URL(s):
   https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify/callback
   ```

6. **Enable Embedded App**
   - Check the box: "Embed app in Shopify admin"

7. **Save Changes**
   - Click "Save" button at the bottom

## Environment Variables

Make sure your `.env` file on the server has:

```env
SHOPIFY_API_KEY=your_api_key_from_partner_dashboard
SHOPIFY_API_SECRET=your_api_secret_from_partner_dashboard
SHOPIFY_APP_URL=https://goldenrod-fox-366864.hostingersite.com
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
NODE_ENV=production
PORT=3000
```

**Important:** `SHOPIFY_APP_URL` must match your App URL exactly (no trailing slash).

## Installation Flow

Once configured correctly, the installation flow works like this:

1. **Store owner clicks "Install" in Shopify App Store or Partner Dashboard**
   - Shopify redirects to: `https://goldenrod-fox-366864.hostingersite.com/?shop=store-name.myshopify.com&hmac=...&host=...`

2. **App checks if shop is installed**
   - If not installed, redirects to: `/api/auth/shopify?shop=store-name.myshopify.com`

3. **OAuth flow begins**
   - App redirects to: `https://store-name.myshopify.com/admin/oauth/authorize?client_id=...&scope=...&redirect_uri=...`

4. **Store owner authorizes**
   - Shopify redirects back to: `https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify/callback?code=...&shop=...&hmac=...`

5. **App processes callback**
   - Exchanges code for access token
   - Stores shop data and session
   - Redirects to: `https://store-name.myshopify.com/admin/apps/{API_KEY}`

6. **App loads successfully**
   - Embedded app loads in Shopify admin
   - No more 403 errors!

## Testing the Installation

1. **Test Installation URL**
   ```
   https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify?shop=your-test-store.myshopify.com
   ```
   - Should redirect to Shopify OAuth page
   - After authorization, should redirect back to your app

2. **Test Direct Access**
   ```
   https://goldenrod-fox-366864.hostingersite.com/?shop=your-test-store.myshopify.com
   ```
   - If not installed: Should redirect to OAuth
   - If installed: Should redirect to embedded app or load frontend

3. **Verify in Database**
   ```sql
   SELECT * FROM shops WHERE shop_domain = 'your-test-store.myshopify.com';
   SELECT * FROM shopify_sessions WHERE shop = 'your-test-store.myshopify.com';
   ```
   - Should have records after successful installation

## Common Issues

### "403 Forbidden" Error
- ✅ Check that "Embed app in Shopify admin" is enabled
- ✅ Verify App URL matches your domain exactly
- ✅ Check that redirect URL is correct
- ✅ Ensure `SHOPIFY_APP_URL` in `.env` matches App URL
- ✅ Verify sessions table exists in database
- ✅ Check server logs for errors

### "Invalid redirect_uri" Error
- ✅ Redirect URL in Partner Dashboard must match exactly: `https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify/callback`
- ✅ No trailing slash
- ✅ Must use HTTPS

### App Doesn't Redirect to OAuth
- ✅ Check server is running
- ✅ Verify API routes are working: `/api/diagnostic`
- ✅ Check server logs for errors
- ✅ Verify database connection

### OAuth Callback Fails
- ✅ Check API_KEY and API_SECRET in `.env`
- ✅ Verify HMAC validation is working
- ✅ Check database can save sessions
- ✅ Review server logs for specific errors

## SSL Certificate

**IMPORTANT:** Your domain must have a valid SSL certificate (HTTPS). 

- Shopify requires HTTPS for all app URLs
- Free SSL certificates are available through Let's Encrypt
- Your hosting provider (Hostinger) should provide SSL setup
- Verify SSL works: `https://goldenrod-fox-366864.hostingersite.com`

## Next Steps

After configuring:
1. ✅ Save settings in Partner Dashboard
2. ✅ Update `.env` file on server
3. ✅ Restart your Node.js server
4. ✅ Test installation flow
5. ✅ Verify app loads in Shopify admin

If you still encounter issues, check the server logs and verify all URLs match exactly between Partner Dashboard and your `.env` file.

