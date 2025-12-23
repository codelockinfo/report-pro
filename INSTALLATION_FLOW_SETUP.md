# Installation Flow Setup - Complete Guide

This document explains the complete installation flow for your Shopify app, based on the PHP reference code you provided, adapted for your Node.js app.

## Your Domain
**Production Domain:** `https://goldenrod-fox-366864.hostingersite.com`

## Installation Flow Overview

The installation flow works in the following steps, similar to your PHP implementation:

### 1. Initial App Access
When a store owner clicks "Install" in Shopify or accesses your app:
```
GET https://goldenrod-fox-366864.hostingersite.com/?shop=store-name.myshopify.com&hmac=...&host=...
```

### 2. Installation Check
The `install` router (`src/routes/install.ts`) checks:
- ✅ If shop parameter exists
- ✅ If shop is already installed in database
- ✅ If it's an embedded app request (has hmac/host)

### 3. OAuth Redirect (if not installed)
If shop is **not installed**, redirects to:
```
GET /api/auth/shopify?shop=store-name.myshopify.com
```

This initiates the OAuth flow with Shopify.

### 4. OAuth Authorization
Shopify redirects user to:
```
https://store-name.myshopify.com/admin/oauth/authorize?client_id=...&scope=...&redirect_uri=...
```

User approves the app permissions.

### 5. OAuth Callback
Shopify redirects back to:
```
GET /api/auth/shopify/callback?code=...&shop=...&hmac=...&timestamp=...
```

### 6. Token Exchange & Storage
The callback handler (`src/controllers/authController.ts`):
- ✅ Validates HMAC signature
- ✅ Exchanges authorization code for access token
- ✅ Fetches shop information from Shopify API
- ✅ Stores session in `shopify_sessions` table
- ✅ Saves shop data in `shops` table

### 7. Redirect to App
After successful installation, redirects to:
```
https://store-name.myshopify.com/admin/apps/{API_KEY}
```

### 8. Embedded App Loads
The app loads inside Shopify admin, and the embedded app authentication middleware verifies the session.

## Key Differences from PHP Implementation

Your Node.js implementation includes these improvements:

1. **Session Storage**: Sessions are stored in a dedicated `shopify_sessions` table, separate from shop data
2. **Session Verification**: Embedded app auth middleware verifies sessions when app loads
3. **Type Safety**: TypeScript provides better error checking
4. **Modern API**: Uses Shopify API v9+ with built-in OAuth handling

## Files Involved

### Installation Route
- **File**: `src/routes/install.ts`
- **Purpose**: Handles initial app access, checks if shop needs installation
- **Endpoint**: `GET /?shop=...`

### OAuth Controller
- **File**: `src/controllers/authController.ts`
- **Endpoints**:
  - `GET /api/auth/shopify` - Initiate OAuth
  - `GET /api/auth/shopify/callback` - Handle OAuth callback

### Session Storage
- **File**: `src/services/sessionStorage.ts`
- **Purpose**: Stores and retrieves Shopify sessions from MySQL

### Embedded App Auth
- **File**: `src/middleware/embeddedAppAuth.ts`
- **Purpose**: Verifies sessions when embedded app loads

## Shopify Partner Dashboard Configuration

Make sure these settings match your domain:

### App URL
```
https://goldenrod-fox-366864.hostingersite.com
```

### Allowed redirection URL(s)
```
https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify/callback
```

### Embedded App
✅ **MUST BE ENABLED** - "Embed app in Shopify admin"

## Environment Variables

Update your `.env` file on the server:

```env
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_APP_URL=https://goldenrod-fox-366864.hostingersite.com
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
NODE_ENV=production
PORT=3000
```

**Important:** `SHOPIFY_APP_URL` must match your App URL in Partner Dashboard exactly (no trailing slash).

## Testing the Installation Flow

### 1. Test OAuth Initiation
```bash
curl "https://goldenrod-fox-366864.hostingersite.com/api/auth/shopify?shop=your-test-store.myshopify.com"
```
Should redirect to Shopify OAuth page.

### 2. Test Installation Check
```bash
curl "https://goldenrod-fox-366864.hostingersite.com/?shop=your-test-store.myshopify.com"
```
- If not installed: Should redirect to OAuth
- If installed: Should redirect to embedded app URL or serve frontend

### 3. Verify Database
After installation, check:
```sql
-- Check shop is saved
SELECT * FROM shops WHERE shop_domain = 'your-test-store.myshopify.com';

-- Check session is stored
SELECT * FROM shopify_sessions WHERE shop = 'your-test-store.myshopify.com';
```

## Troubleshooting

### 403 Forbidden Error
- ✅ Check "Embed app in Shopify admin" is enabled
- ✅ Verify App URL matches domain exactly
- ✅ Check redirect URL is correct
- ✅ Ensure `SHOPIFY_APP_URL` in `.env` matches
- ✅ Verify `shopify_sessions` table exists
- ✅ Check server logs for errors

### Installation Doesn't Start
- ✅ Check server is running
- ✅ Verify database connection
- ✅ Check API routes are accessible: `/api/diagnostic`
- ✅ Review server logs

### OAuth Callback Fails
- ✅ Verify API_KEY and API_SECRET in `.env`
- ✅ Check redirect URL matches Partner Dashboard exactly
- ✅ Review server logs for HMAC validation errors
- ✅ Ensure database can save sessions

### App Doesn't Load After Installation
- ✅ Check shop exists in database with access_token
- ✅ Verify session exists in shopify_sessions table
- ✅ Check embedded app URL format is correct
- ✅ Review browser console for errors
- ✅ Check server logs

## Next Steps

1. ✅ Update `.env` file with correct domain
2. ✅ Configure Shopify Partner Dashboard
3. ✅ Restart server to apply changes
4. ✅ Test installation flow
5. ✅ Verify app loads in Shopify admin

## Additional Notes

- The installation flow is similar to your PHP implementation but uses modern Node.js patterns
- Sessions are stored separately from shop data for better security
- All OAuth validation is handled by Shopify API library
- The app supports both embedded and non-embedded access patterns

For detailed Partner Dashboard setup instructions, see `SHOPIFY_PARTNER_DASHBOARD_SETUP.md`.

