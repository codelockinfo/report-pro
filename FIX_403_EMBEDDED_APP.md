# Fix for 403 Forbidden Error in Embedded Shopify App

## Problem
After installing the app on a Shopify store, accessing it through the Shopify admin shows a "403 Forbidden" error.

## Root Cause
The app was missing **session storage** configuration required for embedded Shopify apps. When Shopify loads an embedded app, it needs to verify the session, and without proper session storage, this verification fails, resulting in a 403 error.

## Solution Implemented

### 1. Created Session Storage Adapter
- **File**: `src/services/sessionStorage.ts`
- Implements MySQL-based session storage for Shopify sessions
- Stores session data including access tokens, shop domain, scopes, etc.
- Provides methods: `storeSession`, `loadSession`, `deleteSession`, `findSessionsByShop`

### 2. Added Sessions Table
- **File**: `src/database/connection.ts`
- Created `shopify_sessions` table in database migrations
- Stores session data with proper indexing for performance
- Automatically created when server starts

### 3. Updated OAuth Callback
- **File**: `src/controllers/authController.ts`
- Now stores sessions in session storage when OAuth callback completes
- Ensures sessions are available for embedded app verification

### 4. Added Embedded App Authentication Middleware
- **File**: `src/middleware/embeddedAppAuth.ts`
- Verifies shop installation when embedded app loads
- Redirects to OAuth if shop is not installed
- Allows requests to proceed if shop is properly authenticated

### 5. Updated Server Configuration
- **File**: `src/server.ts`
- Added embedded app authentication middleware
- Ensures all embedded app requests are properly handled

## How It Works Now

1. **Installation Flow**:
   - Store owner clicks install
   - OAuth flow begins → user authorizes → callback received
   - Session is stored in `shopify_sessions` table
   - Shop data is saved in `shops` table
   - User is redirected to app

2. **Embedded App Loading**:
   - Shopify loads app in iframe with shop parameter
   - Embedded app auth middleware checks if shop is installed
   - If installed, request proceeds to frontend
   - Frontend handles session verification with Shopify App Bridge
   - App loads successfully

## Database Changes

A new table `shopify_sessions` is created with the following structure:
```sql
CREATE TABLE shopify_sessions (
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
)
```

## Testing

1. **Restart the server** to apply changes:
   ```bash
   npm run build
   pm2 restart report-pro  # or your process manager command
   ```

2. **Verify database migration**:
   - Check that `shopify_sessions` table exists
   - Table should be created automatically on server start

3. **Reinstall the app**:
   - Uninstall the app from Shopify admin (if already installed)
   - Install the app again
   - Complete OAuth flow
   - App should now load without 403 error

4. **Verify session storage**:
   - After OAuth callback, check `shopify_sessions` table
   - Should contain a session record for the shop

## Important Notes

- **Session storage is required** for embedded Shopify apps
- Sessions are stored separately from shop data in the `shops` table
- The `shops` table stores access tokens for API calls
- The `shopify_sessions` table stores full session data for embedded app authentication
- Both are needed for the app to work correctly

## Troubleshooting

If you still see 403 errors:

1. **Check database tables exist**:
   ```sql
   SHOW TABLES LIKE 'shopify_sessions';
   SELECT * FROM shopify_sessions LIMIT 1;
   ```

2. **Check server logs** for errors:
   ```bash
   pm2 logs report-pro
   ```

3. **Verify OAuth callback stored session**:
   - Check `shopify_sessions` table after installation
   - Should have a record for your shop

4. **Verify environment variables**:
   - `SHOPIFY_API_KEY` is set
   - `SHOPIFY_API_SECRET` is set
   - `SHOPIFY_APP_URL` matches your app URL in Shopify settings

5. **Check Shopify app settings**:
   - "Embed app in Shopify admin" should be checked
   - App URL should match your domain exactly
   - Callback URL should be: `https://your-domain.com/api/auth/shopify/callback`

## Files Modified

- `src/services/sessionStorage.ts` (new)
- `src/database/connection.ts` (updated)
- `src/controllers/authController.ts` (updated)
- `src/middleware/embeddedAppAuth.ts` (new)
- `src/server.ts` (updated)

