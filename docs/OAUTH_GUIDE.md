# Shopify OAuth Installation Guide

## Overview

This guide explains how to implement Shopify OAuth 2.0 authentication for your app. The OAuth flow allows merchants to securely authorize your app to access their Shopify store data.

## OAuth Flow Diagram

```
┌──────────┐         ┌──────────┐         ┌──────────┐
│  User    │         │   Your   │         │ Shopify  │
│ Browser  │         │   App    │         │   API    │
└────┬─────┘         └────┬─────┘         └────┬─────┘
     │                    │                    │
     │ 1. Visit install   │                    │
     │    URL with shop   │                    │
     ├───────────────────>│                    │
     │                    │                    │
     │ 2. Redirect to     │                    │
     │    Shopify OAuth   │                    │
     │<───────────────────┤                    │
     │                    │                    │
     │ 3. User authorizes  │                    │
     │    app in Shopify   │                    │
     ├────────────────────────────────────────>│
     │                    │                    │
     │ 4. Redirect with    │                    │
     │    code + state     │                    │
     │<────────────────────────────────────────┤
     │                    │                    │
     │ 5. Exchange code   │                    │
     │    for token        │                    │
     ├───────────────────>│                    │
     │                    │                    │
     │                    │ 6. POST to        │
     │                    │    /oauth/         │
     │                    │    access_token    │
     │                    ├───────────────────>│
     │                    │                    │
     │                    │ 7. Return access  │
     │                    │    token           │
     │                    │<───────────────────┤
     │                    │                    │
     │ 8. Save token &    │                    │
     │    redirect to app  │                    │
     │<───────────────────┤                    │
     │                    │                    │
```

## Implementation Files

### 1. `oauth_install.php` - Installation Handler

This file initiates the OAuth flow when a merchant wants to install your app.

**Features:**
- Validates shop domain format
- Generates secure CSRF state token
- Builds Shopify OAuth authorization URL
- Redirects merchant to Shopify for authorization

**Usage:**
```
https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-shop.myshopify.com
```

### 2. `oauth_callback.php` - Callback Handler

This file handles the callback from Shopify after authorization.

**Features:**
- Validates state token (CSRF protection)
- Validates HMAC signature
- Exchanges authorization code for access token
- Saves shop and token to database
- Creates user session
- Redirects to app dashboard

**Configuration:**
Set this as your callback URL in Shopify Partner Dashboard:
```
https://reportpro.codelocksolutions.com/oauth_callback.php
```

### 3. `oauth_example.php` - Complete Standalone Example

A complete, self-contained OAuth implementation that includes:
- Installation handler
- Callback handler
- Database operations
- HTML form for testing

## Step-by-Step Setup

### Step 1: Configure Shopify App

1. Go to [Shopify Partner Dashboard](https://partners.shopify.com)
2. Create a new app or select existing app
3. Go to "App setup" → "Client credentials"
4. Copy your **API key** and **API secret**
5. Go to "App setup" → "URLs"
6. Set **Allowed redirection URL(s)**:
   ```
   https://reportpro.codelocksolutions.com/oauth_callback.php
   ```

### Step 2: Configure Your Application

Edit `config/config.php`:

```php
'shopify' => [
    'api_key' => 'your_api_key_here',
    'api_secret' => 'your_api_secret_here',
    'scopes' => 'read_orders,read_products,read_customers,read_inventory,read_transactions,read_analytics',
    'redirect_uri' => 'https://reportpro.codelocksolutions.com/oauth_callback.php',
],
```

### Step 3: Set Required Scopes

Choose the scopes your app needs:

**Common Scopes:**
- `read_orders` - Read order data
- `read_products` - Read product data
- `read_customers` - Read customer data
- `read_inventory` - Read inventory data
- `read_transactions` - Read transaction data
- `read_analytics` - Read analytics data
- `write_products` - Modify products (if needed)
- `write_orders` - Modify orders (if needed)

**Format:**
```php
'scopes' => 'read_orders,read_products,read_customers',
```

### Step 4: Test Installation

1. Visit installation URL:
   ```
   https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-test-shop.myshopify.com
   ```

2. You'll be redirected to Shopify authorization page

3. Click "Install app" or "Allow"

4. You'll be redirected back to your callback URL

5. Check database - shop should be saved with access token

## Security Features

### 1. CSRF Protection (State Token)

- Random 32-byte token generated
- Stored in session
- Validated on callback
- Prevents cross-site request forgery attacks

### 2. HMAC Validation

- Shopify signs callback parameters with HMAC
- We validate the signature using API secret
- Ensures request came from Shopify
- Prevents parameter tampering

### 3. Shop Domain Validation

- Validates format: `shopname.myshopify.com`
- Prevents malicious shop domains
- Ensures proper Shopify store

## Error Handling

### Common Errors

**1. "Missing shop parameter"**
- Solution: Ensure `?shop=your-shop.myshopify.com` is in URL

**2. "Invalid shop domain"**
- Solution: Use format: `shopname.myshopify.com`

**3. "Invalid state parameter"**
- Solution: Session expired or CSRF attack. Try installing again.

**4. "Invalid HMAC signature"**
- Solution: Check API secret is correct. Request may be tampered with.

**5. "Failed to get access token"**
- Solution: Check API credentials. Code may have expired (codes expire quickly).

**6. "Access token not received"**
- Solution: Shopify didn't return token. Check API credentials and scopes.

## Testing

### Local Development

For local testing, use a tool like ngrok:

```bash
# Install ngrok
# Start your local server
php -S localhost:8000

# In another terminal
ngrok http 8000

# Use ngrok URL in Shopify app settings
# Example: https://abc123.ngrok.io/oauth_callback.php
```

### Production Testing

1. Use a development store
2. Install app via installation URL
3. Verify token is saved
4. Test API calls with token
5. Check logs for any errors

## Logging

OAuth events are logged to `storage/oauth.log`:

```
[2024-01-15 10:30:45] OAuth installation initiated | Data: {"shop":"test.myshopify.com","state":"abc123..."}
[2024-01-15 10:31:02] OAuth callback received | Data: {"shop":"test.myshopify.com","has_code":true}
[2024-01-15 10:31:03] OAuth installation completed successfully | Data: {"shop":"test.myshopify.com","shop_id":1}
```

## Database Schema

Ensure your `shops` table exists:

```sql
CREATE TABLE shops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_domain VARCHAR(255) UNIQUE NOT NULL,
    store_name VARCHAR(255),
    access_token TEXT,
    scope TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Best Practices

1. **Always validate HMAC** - Never skip this security check
2. **Use HTTPS** - Required in production
3. **Store tokens securely** - Encrypt if possible
4. **Handle token refresh** - Tokens don't expire, but handle revocations
5. **Log OAuth events** - Helps with debugging
6. **Validate shop domain** - Prevent malicious domains
7. **Use state tokens** - CSRF protection is essential
8. **Handle errors gracefully** - Show user-friendly messages

## Troubleshooting

### App won't install
- Check API credentials are correct
- Verify redirect URI matches exactly
- Ensure HTTPS in production
- Check scopes are valid

### Callback not working
- Verify callback URL in Shopify dashboard
- Check server logs for errors
- Ensure session is working
- Verify HMAC validation

### Token not saving
- Check database connection
- Verify table structure
- Check for SQL errors in logs
- Ensure proper error handling

## Additional Resources

- [Shopify OAuth Documentation](https://shopify.dev/apps/auth/oauth)
- [Shopify App Bridge](https://shopify.dev/apps/tools/app-bridge)
- [Shopify API Scopes](https://shopify.dev/api/usage/access-scopes)

## Support

For issues:
1. Check error logs: `storage/oauth.log`
2. Verify configuration
3. Test with development store
4. Review Shopify Partner Dashboard settings

