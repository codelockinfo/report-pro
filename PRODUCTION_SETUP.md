# 🚀 Production Server Setup Guide

## Database Connection Error Fix

If you're seeing **"Database connection failed. Please check your configuration."** error, follow these steps:

## Step 1: Create `.env` File on Production Server

1. **SSH into your production server** (reportpro.codelocksolutions.com)

2. **Navigate to your project directory**:
   ```bash
   cd /path/to/report-pro
   ```

3. **Copy the example file**:
   ```bash
   cp .env.example .env
   ```

4. **Edit the `.env` file** with your actual database credentials:
   ```bash
   nano .env
   ```

5. **Add your database credentials**:
   ```env
   # Database Configuration
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=u402017191_report_pro
   DB_USER=your_database_username
   DB_PASSWORD=your_database_password
   
   # Shopify App Credentials
   SHOPIFY_API_KEY=your_shopify_api_key
   SHOPIFY_API_SECRET=your_shopify_api_secret
   SHOPIFY_REDIRECT_URI=https://reportpro.codelocksolutions.com/auth/callback
   
   # Application Configuration
   APP_URL=https://reportpro.codelocksolutions.com
   APP_SECRET_KEY=your_secret_key_here
   APP_ENCRYPTION_KEY=your_encryption_key_here
   ```

6. **Save and exit** (Ctrl+X, then Y, then Enter in nano)

## Step 2: Import Database

1. **Upload the SQL file** to your server:
   ```bash
   # Upload u402017191_report_pro.sql to your server
   ```

2. **Import the database**:
   ```bash
   mysql -u your_database_username -p u402017191_report_pro < u402017191_report_pro.sql
   ```
   
   Or use phpMyAdmin:
   - Go to phpMyAdmin
   - Select database `u402017191_report_pro`
   - Click "Import"
   - Choose `u402017191_report_pro.sql`
   - Click "Go"

## Step 3: Verify Database Connection

1. **Check database exists**:
   ```bash
   mysql -u your_database_username -p -e "SHOW DATABASES LIKE 'u402017191_report_pro';"
   ```

2. **Check tables exist**:
   ```bash
   mysql -u your_database_username -p u402017191_report_pro -e "SHOW TABLES;"
   ```

   You should see tables like:
   - `shops`
   - `reports`
   - `schedules`
   - `exports`
   - etc.

## Step 4: Set File Permissions

1. **Set proper permissions on `.env` file**:
   ```bash
   chmod 600 .env
   chown www-data:www-data .env
   ```

2. **Set permissions on storage directory**:
   ```bash
   chmod -R 755 storage
   chown -R www-data:www-data storage
   ```

## Step 5: Test the Application

1. **Visit your app URL**:
   ```
   https://reportpro.codelocksolutions.com
   ```

2. **Install the app on a test Shopify store**:
   ```
   https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-test-shop.myshopify.com
   ```

## Common Issues & Solutions

### Issue 1: "Access denied for user"

**Solution**: Check your database username and password in `.env` file.

### Issue 2: "Unknown database 'u402017191_report_pro'"

**Solution**: 
- Create the database:
  ```bash
  mysql -u root -p -e "CREATE DATABASE u402017191_report_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  ```
- Then import the SQL file

### Issue 3: "Connection refused" or "Can't connect to MySQL server"

**Solution**: 
- Check `DB_HOST` in `.env` file
- For local MySQL: use `127.0.0.1` or `localhost`
- For remote MySQL: use the actual hostname/IP
- Check MySQL is running: `systemctl status mysql`

### Issue 4: Database connection works but app still shows error

**Solution**: 
- Clear PHP opcache: `php -r "opcache_reset();"`
- Restart PHP-FPM: `systemctl restart php-fpm` or `systemctl restart php8.1-fpm`
- Check error logs: `tail -f /var/log/php-fpm/error.log`

## Database Credentials for cPanel/Shared Hosting

If you're using cPanel or shared hosting:

1. **Get credentials from cPanel**:
   - Go to cPanel → MySQL Databases
   - Find your database name (usually `username_report_pro`)
   - Note the database username and password

2. **Update `.env` file**:
   ```env
   DB_HOST=localhost
   DB_NAME=username_report_pro
   DB_USER=username_dbuser
   DB_PASSWORD=your_cpanel_db_password
   ```

## Security Checklist

- [ ] `.env` file created with correct credentials
- [ ] `.env` file permissions set to 600 (read/write for owner only)
- [ ] `.env` is in `.gitignore` (should not be committed)
- [ ] Database imported successfully
- [ ] Database user has proper permissions
- [ ] Application can connect to database
- [ ] Error logs are being written (check `/storage/oauth.log`)

## Testing Database Connection

You can test the database connection manually:

```php
<?php
// test_db.php (delete after testing)
require_once 'config/config.php';
$config = require 'config/config.php';
$db = $config['database'];

try {
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['password']);
    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
```

## Need Help?

If you're still having issues:

1. **Check error logs**:
   ```bash
   tail -f storage/oauth.log
   tail -f /var/log/php-fpm/error.log
   ```

2. **Enable error display temporarily** (for debugging only):
   - Edit `index.php`
   - Change `ini_set('display_errors', 0);` to `ini_set('display_errors', 1);`
   - **Remember to disable it again after fixing!**

3. **Verify .env file is being loaded**:
   - Add a test: `echo getenv('DB_HOST');` in `index.php`
   - Should output your database host

