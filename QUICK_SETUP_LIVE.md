# ⚡ Quick Setup for Live Server

Since you already have the database `u402017191_report_pro` on your live server, you just need to create the `.env` file with the correct credentials.

## Step 1: Get Your Database Credentials

You need to find these from your hosting provider (cPanel, Plesk, or hosting dashboard):

1. **Database Username** (usually starts with your cPanel username)
2. **Database Password** (the password for the database user)
3. **Database Host** (usually `localhost` or `127.0.0.1`)

### Where to find them:

**cPanel:**
- Go to **MySQL Databases** section
- Look for your database user (usually `username_dbuser`)
- The database name is: `u402017191_report_pro`

**Or check your existing database configuration files** (if you have other PHP apps working)

## Step 2: Create `.env` File on Live Server

1. **SSH into your server** or use **File Manager** in cPanel

2. **Navigate to your project root**:
   ```
   /home/yourusername/public_html/report-pro
   ```
   (or wherever your files are located)

3. **Create `.env` file**:
   ```bash
   nano .env
   ```
   
   Or use File Manager → Create New File → `.env`

4. **Add this content** (replace with YOUR actual credentials):
   ```env
   # Database Configuration
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=u402017191_report_pro
   DB_USER=your_actual_database_username
   DB_PASSWORD=your_actual_database_password
   
   # Shopify App Credentials
   SHOPIFY_API_KEY=your_shopify_api_key
   SHOPIFY_API_SECRET=your_shopify_api_secret
   SHOPIFY_REDIRECT_URI=https://reportpro.codelocksolutions.com/auth/callback
   
   # Application Configuration
   APP_URL=https://reportpro.codelocksolutions.com
   APP_SECRET_KEY=generate-a-random-secret-key-here
   APP_ENCRYPTION_KEY=generate-another-random-key-here
   ```

5. **Save the file**

6. **Set permissions** (via SSH):
   ```bash
   chmod 600 .env
   ```

## Step 3: Test Database Connection

I've created a test script for you. Upload `test_db_connection.php` to your server and visit it in browser:

```
https://reportpro.codelocksolutions.com/test_db_connection.php
```

This will tell you if the connection works!

## Common Issues

### If you don't know your database username/password:

1. **Check cPanel → MySQL Databases**
2. **Check existing `.env` files** from other projects
3. **Check your hosting provider's documentation**
4. **Contact your hosting support**

### If connection still fails:

- Make sure database user has permissions on `u402017191_report_pro` database
- Check if database host is `localhost` or `127.0.0.1` (try both)
- Verify database name is exactly `u402017191_report_pro` (case-sensitive on Linux)

## After Setup

Once `.env` is created with correct credentials:
1. Delete `test_db_connection.php` (security)
2. Visit your app: `https://reportpro.codelocksolutions.com`
3. The database error should be gone! ✅

