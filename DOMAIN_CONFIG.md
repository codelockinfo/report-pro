# Domain Configuration - reportpro.codelocksolutions.com

## Production Domain

**Domain**: `reportpro.codelocksolutions.com`

## Important URLs

### Application URLs

- **App URL**: `https://reportpro.codelocksolutions.com`
- **Installation URL**: `https://reportpro.codelocksolutions.com/oauth_install.php?shop=SHOP.myshopify.com`
- **OAuth Callback URL**: `https://reportpro.codelocksolutions.com/oauth_callback.php`
- **Dashboard URL**: `https://reportpro.codelocksolutions.com/dashboard`
- **Auth Callback (MVC)**: `https://reportpro.codelocksolutions.com/auth/callback`

### Webhook URLs

- **App Uninstalled**: `https://reportpro.codelocksolutions.com/webhooks/app/uninstalled`
- **Customer Data Request**: `https://reportpro.codelocksolutions.com/webhooks/customers/data_request`
- **Customer Redact**: `https://reportpro.codelocksolutions.com/webhooks/customers/redact`
- **Shop Redact**: `https://reportpro.codelocksolutions.com/webhooks/shop/redact`

## Shopify Partner Dashboard Configuration

### App Setup → URLs

**App URL**:
```
https://reportpro.codelocksolutions.com
```

**Allowed redirection URL(s)**:
```
https://reportpro.codelocksolutions.com/oauth_callback.php,https://reportpro.codelocksolutions.com/auth/callback
```
⚠️ **Important**: Enter as comma-separated (no line breaks, no spaces after comma)

### App Setup → Webhooks

Add the following webhooks:

1. **App uninstalled**
   - URL: `https://reportpro.codelocksolutions.com/webhooks/app/uninstalled`
   - Format: JSON

2. **Customer data request**
   - URL: `https://reportpro.codelocksolutions.com/webhooks/customers/data_request`
   - Format: JSON

3. **Customer redaction**
   - URL: `https://reportpro.codelocksolutions.com/webhooks/customers/redact`
   - Format: JSON

4. **Shop redaction**
   - URL: `https://reportpro.codelocksolutions.com/webhooks/shop/redact`
   - Format: JSON

## Configuration Files

### config/config.php

The following values are already configured:

```php
'app_url' => 'https://reportpro.codelocksolutions.com',
'shopify' => [
    'redirect_uri' => 'https://reportpro.codelocksolutions.com/auth/callback',
],
```

### Environment Variables (Optional)

You can override these with environment variables:

```bash
APP_URL=https://reportpro.codelocksolutions.com
SHOPIFY_REDIRECT_URI=https://reportpro.codelocksolutions.com/auth/callback
```

## SSL Certificate

**Important**: Ensure SSL certificate is properly configured for:
- `reportpro.codelocksolutions.com`
- All subdomains (if needed)

Shopify requires HTTPS for all OAuth and webhook endpoints.

## Testing Installation

### Test OAuth Flow

1. Visit installation URL:
   ```
   https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-test-shop.myshopify.com
   ```

2. Or use MVC route:
   ```
   https://reportpro.codelocksolutions.com/auth/install?shop=your-test-shop.myshopify.com
   ```

### Verify Configuration

1. Check `config/config.php` has correct domain
2. Verify Shopify Partner Dashboard URLs match
3. Test OAuth installation flow
4. Verify webhooks are receiving requests
5. Check SSL certificate is valid

## DNS Configuration

Ensure DNS is properly configured:

```
A Record: reportpro.codelocksolutions.com → [Server IP]
```

Or if using CNAME:

```
CNAME: reportpro.codelocksolutions.com → [Hostname]
```

## Server Configuration

### Apache Virtual Host Example

```apache
<VirtualHost *:443>
    ServerName reportpro.codelocksolutions.com
    DocumentRoot /path/to/report-pro
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    <Directory /path/to/report-pro>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Server Block Example

```nginx
server {
    listen 443 ssl;
    server_name reportpro.codelocksolutions.com;
    root /path/to/report-pro;
    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Checklist

Before going live:

- [ ] SSL certificate installed and valid
- [ ] Domain DNS configured correctly
- [ ] `config/config.php` updated with domain
- [ ] Shopify Partner Dashboard URLs configured
- [ ] Webhooks configured in Shopify
- [ ] OAuth installation tested
- [ ] Webhook endpoints tested
- [ ] Database connection working
- [ ] Storage directories writable
- [ ] Cron jobs configured

## Support

If you encounter issues:

1. Verify domain is accessible: `curl -I https://reportpro.codelocksolutions.com`
2. Check SSL certificate: `openssl s_client -connect reportpro.codelocksolutions.com:443`
3. Review error logs: `storage/oauth.log`
4. Verify Shopify app settings match URLs above

