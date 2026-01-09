# Installation Guide - Report Pro

## Prerequisites

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite OR Nginx
- Composer
- SSL Certificate (for production)
- Shopify Partner Account

## Step-by-Step Installation

### 1. Download/Clone the Application

```bash
git clone <repository-url>
cd report-pro
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Database Setup

#### Create Database

```sql
CREATE DATABASE u402017191_report_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Import Schema

```bash
mysql -u root -p u402017191_report_pro < u402017191_report_pro.sql
```

Or via phpMyAdmin:
1. Select database
2. Go to Import tab
3. Choose `u402017191_report_pro.sql`
4. Click Go

### 4. Configure Application

#### Copy Environment File

```bash
cp .env.example .env
```

#### Edit Configuration

Edit `config/config.php` or set environment variables:

```php
// Database
'DB_HOST' => '127.0.0.1',
'DB_NAME' => 'u402017191_report_pro',
'DB_USER' => 'root',
'DB_PASSWORD' => 'your_password',

// Shopify App
'SHOPIFY_API_KEY' => 'your_api_key',
'SHOPIFY_API_SECRET' => 'your_api_secret',
'SHOPIFY_REDIRECT_URI' => 'https://yourdomain.com/auth/callback',
```

### 5. Shopify App Setup

#### Create App in Partner Dashboard

1. Go to https://partners.shopify.com
2. Navigate to Apps
3. Click "Create app"
4. Choose "Custom app"
5. Fill in app details:
   - App name: Report Pro
   - App URL: `https://yourdomain.com`
   - Allowed redirection URL(s): `https://yourdomain.com/auth/callback`

#### Configure Scopes

Required scopes:
- `read_orders`
- `read_products`
- `read_customers`
- `read_inventory`
- `read_transactions`
- `read_analytics`

#### Get API Credentials

1. In app settings, go to "Client credentials"
2. Copy API key and API secret
3. Update `config/config.php`

### 6. Webhook Configuration

In Shopify Partner Dashboard:

1. Go to your app → Webhooks
2. Add webhooks:
   - **App uninstalled**: `https://yourdomain.com/webhooks/app/uninstalled`
   - **Customer data request**: `https://yourdomain.com/webhooks/customers/data_request`
   - **Customer redaction**: `https://yourdomain.com/webhooks/customers/redact`
   - **Shop redaction**: `https://yourdomain.com/webhooks/shop/redact`

### 7. Web Server Configuration

#### Apache Configuration

Ensure `.htaccess` is working:

```apache
<Directory /path/to/report-pro>
    AllowOverride All
    Require all granted
</Directory>
```

Enable mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/report-pro;
    index index.php;

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

### 8. Set Up Storage Directories

```bash
mkdir -p storage/exports
chmod 755 storage/exports
chown www-data:www-data storage/exports  # Adjust user as needed
```

### 9. Set Up Cron Jobs

Edit crontab:
```bash
crontab -e
```

Add:
```bash
# Check scheduled reports every minute
* * * * * php /path/to/report-pro/cron/scheduled_reports.php >> /path/to/report-pro/storage/cron.log 2>&1

# Check bulk operations every 5 minutes
*/5 * * * * php /path/to/report-pro/cron/bulk_operations.php >> /path/to/report-pro/storage/cron.log 2>&1
```

### 10. SSL Certificate (Production)

#### Using Let's Encrypt

```bash
sudo certbot --nginx -d yourdomain.com
# or
sudo certbot --apache -d yourdomain.com
```

### 11. Test Installation

#### Test OAuth Flow

1. Navigate to: `https://yourdomain.com/auth/install?shop=your-test-shop.myshopify.com`
2. Authorize the app
3. Should redirect to dashboard

#### Test Database Connection

Create test file `test_db.php`:
```php
<?php
require_once 'config/database.php';
echo "Database connection successful!";
```

#### Test Webhooks

Use Shopify webhook testing tool or ngrok for local testing.

### 12. Production Checklist

- [ ] SSL certificate installed
- [ ] Environment variables set
- [ ] Database credentials secure
- [ ] Cron jobs configured
- [ ] Storage directories writable
- [ ] Error logging enabled
- [ ] Webhooks configured
- [ ] API credentials set
- [ ] Test OAuth flow
- [ ] Test report generation
- [ ] Test export functionality

## Troubleshooting

### Database Connection Error

**Error**: "Database connection failed"

**Solutions**:
1. Check database credentials in `config/config.php`
2. Verify MySQL is running: `sudo systemctl status mysql`
3. Check database exists: `SHOW DATABASES;`
4. Verify user permissions

### OAuth Not Working

**Error**: "Invalid HMAC" or redirect issues

**Solutions**:
1. Verify API key and secret match Shopify app
2. Check redirect URI matches exactly
3. Ensure HTTPS in production
4. Verify scopes are correct

### Cron Jobs Not Running

**Solutions**:
1. Check cron service: `sudo systemctl status cron`
2. Verify file paths are absolute
3. Check file permissions
4. Review cron logs
5. Test manually: `php /path/to/cron/scheduled_reports.php`

### Permission Errors

**Error**: "Permission denied" on file operations

**Solutions**:
```bash
chmod -R 755 storage/
chown -R www-data:www-data storage/  # Adjust user
```

### 500 Internal Server Error

**Solutions**:
1. Check PHP error logs
2. Enable error display (development only)
3. Verify .htaccess is working
4. Check file permissions
5. Verify PHP version: `php -v`

## Support

For installation issues:
1. Check error logs
2. Verify all prerequisites
3. Review configuration
4. Contact support with error details

## Next Steps

After installation:
1. Read [README.md](README.md) for usage
2. Review [ARCHITECTURE.md](ARCHITECTURE.md) for system overview
3. Check [SHOPIFY_APP_CHECKLIST.md](SHOPIFY_APP_CHECKLIST.md) for app store submission

