# Shopify App Setup Guide - reportpro.codelocksolutions.com

## Quick Setup Checklist

Use this guide to configure your Shopify app in the Partner Dashboard.

## Step 1: App Information

1. Go to [Shopify Partner Dashboard](https://partners.shopify.com)
2. Navigate to your app (or create new app)
3. Fill in app details:
   - **App name**: Report Pro
   - **App URL**: `https://reportpro.codelocksolutions.com`
   - **Allowed redirection URL(s)**: 
     ```
     https://reportpro.codelocksolutions.com/oauth_callback.php,https://reportpro.codelocksolutions.com/auth/callback
     ```
     ⚠️ **Important**: Enter as comma-separated (no line breaks)

## Step 2: API Credentials

1. Go to **App setup** → **Client credentials**
2. Copy your credentials:
   - **API key** (Client ID)
   - **API secret key** (Client secret)
3. Update `config/config.php`:
   ```php
   'shopify' => [
       'api_key' => 'YOUR_API_KEY_HERE',
       'api_secret' => 'YOUR_API_SECRET_HERE',
   ],
   ```

## Step 3: App Scopes

Required scopes for Report Pro:

```
read_orders
read_products
read_customers
read_inventory
read_transactions
read_analytics
```

These are already configured in `config/config.php`.

## Step 4: Webhooks

Configure the following webhooks in **App setup** → **Webhooks**:

### 1. App Uninstalled
- **Event**: App uninstalled
- **URL**: `https://reportpro.codelocksolutions.com/webhooks/app/uninstalled`
- **Format**: JSON
- **API version**: 2024-01

### 2. Customer Data Request (GDPR)
- **Event**: Customer data request
- **URL**: `https://reportpro.codelocksolutions.com/webhooks/customers/data_request`
- **Format**: JSON
- **API version**: 2024-01

### 3. Customer Redaction (GDPR)
- **Event**: Customer redaction
- **URL**: `https://reportpro.codelocksolutions.com/webhooks/customers/redact`
- **Format**: JSON
- **API version**: 2024-01

### 4. Shop Redaction (GDPR)
- **Event**: Shop redaction
- **URL**: `https://reportpro.codelocksolutions.com/webhooks/shop/redact`
- **Format**: JSON
- **API version**: 2024-01

## Step 5: Test Installation

### Using Standalone OAuth Script

1. Visit:
   ```
   https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-test-shop.myshopify.com
   ```

2. You'll be redirected to Shopify authorization page

3. Click "Install app"

4. You'll be redirected back to your app

### Using MVC Route

1. Visit:
   ```
   https://reportpro.codelocksolutions.com/auth/install?shop=your-test-shop.myshopify.com
   ```

2. Follow the same flow

## Step 6: Verify Installation

After installation, verify:

1. **Database**: Check `shops` table has new entry
2. **Session**: User should be logged in
3. **Dashboard**: Should redirect to `/dashboard`
4. **API Access**: Test making API calls with stored token

## Step 7: App Store Listing (Optional)

If submitting to Shopify App Store:

1. Go to **App listing**
2. Fill in:
   - App description
   - Screenshots
   - Support information
   - Pricing
3. Review [SHOPIFY_APP_CHECKLIST.md](SHOPIFY_APP_CHECKLIST.md)

## Common Issues

### "Invalid redirect_uri"

**Problem**: Redirect URI doesn't match Shopify settings

**Solution**: 
- Verify URL in Shopify Partner Dashboard matches exactly
- Check for trailing slashes
- Ensure HTTPS is used

### "Invalid HMAC"

**Problem**: HMAC signature validation fails

**Solution**:
- Verify API secret is correct
- Check request hasn't been tampered with
- Ensure HMAC validation code is working

### "Access token not received"

**Problem**: Token exchange fails

**Solution**:
- Verify API credentials are correct
- Check authorization code hasn't expired
- Review error logs: `storage/oauth.log`

### Webhooks not receiving

**Problem**: Webhooks not being called

**Solution**:
- Verify webhook URLs are accessible
- Check SSL certificate is valid
- Ensure webhook endpoints return 200 status
- Review webhook logs

## Testing Checklist

- [ ] OAuth installation works
- [ ] Access token is saved to database
- [ ] User session is created
- [ ] Dashboard loads after installation
- [ ] Webhooks are receiving requests
- [ ] API calls work with stored token
- [ ] App uninstall webhook works
- [ ] GDPR webhooks work

## Production Checklist

Before going live:

- [ ] SSL certificate installed
- [ ] Domain DNS configured
- [ ] API credentials set in config
- [ ] Webhooks configured
- [ ] Database connection working
- [ ] Error logging enabled
- [ ] Cron jobs configured
- [ ] Storage directories writable
- [ ] Tested with real Shopify store

## Support

For issues:
1. Check error logs: `storage/oauth.log`
2. Review Shopify Partner Dashboard settings
3. Verify domain and SSL configuration
4. Test with development store first

## Next Steps

After setup:
1. Read [README.md](README.md) for usage
2. Review [INSTALLATION.md](INSTALLATION.md) for deployment
3. Check [ARCHITECTURE.md](ARCHITECTURE.md) for system overview

