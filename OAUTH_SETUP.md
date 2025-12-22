# Shopify OAuth Setup Guide

This app uses **Shopify OAuth 2.0** for public app installation. Each store that installs the app gets its own access token stored in the database.

## How It Works

1. **Store owner visits install URL**: `https://your-app.com/api/auth/shopify?shop=store-name.myshopify.com`
2. **OAuth flow begins**: User is redirected to Shopify to authorize the app
3. **Callback received**: Shopify redirects back with authorization code
4. **Token exchange**: App exchanges code for access token
5. **Shop data saved**: Access token, shop domain, and store name are saved to database
6. **App installed**: Store owner is redirected to the app

## Required Scopes

The app requests these scopes:
- `read_orders` - Read order data
- `read_transactions` - Read transaction data
- `read_products` - Read product data
- `read_customers` - Read customer data
- `read_inventory` - Read inventory levels
- `read_locations` - Read store locations
- `write_webhooks` - Create webhooks (for real-time updates)

## Environment Variables

You still need `SHOPIFY_API_KEY` and `SHOPIFY_API_SECRET` in your `.env` file. These are used to:
- Initiate the OAuth flow
- Verify OAuth callbacks
- Exchange authorization codes for access tokens

**Important**: These are your app's credentials (same for all stores), NOT store-specific tokens.

```env
SHOPIFY_API_KEY=your_app_api_key
SHOPIFY_API_SECRET=your_app_api_secret
SHOPIFY_SCOPES=read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
SHOPIFY_APP_URL=https://your-app-url.com
```

## Database Storage

When a store installs the app, the following data is stored in the `shops` table:

- `shop_domain` - Store's myshopify.com domain
- `store_name` - Store's display name
- `access_token` - Store-specific access token (encrypted in production)
- `scope` - Granted permissions
- `created_at` - Installation timestamp
- `updated_at` - Last update timestamp

## Installation Flow

### 1. Store Owner Clicks Install

```
GET /api/auth/shopify?shop=store-name.myshopify.com
```

### 2. Redirected to Shopify

User is redirected to:
```
https://store-name.myshopify.com/admin/oauth/authorize?client_id=...&scope=...&redirect_uri=...
```

### 3. User Authorizes

Store owner approves the requested permissions.

### 4. Callback Received

```
GET /api/auth/shopify/callback?code=...&shop=...&hmac=...&timestamp=...
```

### 5. Token Stored

Access token is saved to database and user is redirected to app.

## Making API Calls

Once installed, use the shop's access token to make API calls:

```typescript
const shop = await getShopByDomain('store-name.myshopify.com');
const session = shopify.session.customAppSession(shop.shop_domain);
session.accessToken = shop.access_token;

const client = new shopify.clients.Graphql({ session });
// Make API calls...
```

## Security Notes

1. **Access tokens are sensitive** - Store them securely
2. **HMAC verification** - All OAuth callbacks are verified
3. **Shop isolation** - Each shop can only access its own data
4. **Token refresh** - Offline tokens don't expire (unless revoked)

## Testing OAuth Flow

1. Start your app: `npm run dev`
2. Visit: `http://localhost:3000/api/auth/shopify?shop=your-test-store.myshopify.com`
3. Complete OAuth flow
4. Check database: `SELECT * FROM shops WHERE shop_domain = 'your-test-store.myshopify.com'`

## Troubleshooting

### "Shop not found" error
- Store hasn't installed the app yet
- Visit the install URL first

### "Invalid OAuth callback"
- Check HMAC verification
- Ensure callback URL matches Shopify app settings

### "Failed to obtain access token"
- Verify API_KEY and API_SECRET in .env
- Check scopes are correct
- Ensure redirect URI matches app settings

