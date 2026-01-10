# 📋 Ready-to-Paste .env File

I've created a `.env.ready-to-paste` file with your Shopify credentials already filled in!

## ✅ What's Already Done

- ✅ Shopify API Key: `a53fcb46618232fcc1aca1bf585e700d`
- ✅ Shopify API Secret: `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`
- ✅ Redirect URI: `https://reportpro.codelocksolutions.com/auth/callback`
- ✅ App URL: `https://reportpro.codelocksolutions.com`
- ✅ Database name: `u402017191_report_pro`

## ⚠️ What You Need to Fill In

You need to replace these placeholders with your actual database credentials:

1. **DB_USER** - Your database username (from cPanel/hosting)
2. **DB_PASSWORD** - Your database password (from cPanel/hosting)
3. **APP_SECRET_KEY** - Generate a random string (for security)
4. **APP_ENCRYPTION_KEY** - Generate another random string (for security)

## 🚀 How to Use

### Step 1: Copy the file content

Open `.env.ready-to-paste` and copy all the content.

### Step 2: Create .env on your live server

1. **SSH into your server** or use **File Manager** in cPanel
2. Navigate to your project root: `/path/to/report-pro`
3. Create new file: `.env`
4. Paste the content

### Step 3: Fill in database credentials

Replace these lines with your actual database info:
```env
DB_USER=your_actual_database_username
DB_PASSWORD=your_actual_database_password
```

### Step 4: Generate secret keys (optional but recommended)

You can use these commands to generate random keys:

**On Linux/Mac:**
```bash
openssl rand -hex 32  # For APP_SECRET_KEY
openssl rand -hex 32  # For APP_ENCRYPTION_KEY
```

**Or use an online generator:**
- Visit: https://randomkeygen.com/
- Use "CodeIgniter Encryption Keys" or "Fort Knox Passwords"
- Copy two different keys

Replace in `.env`:
```env
APP_SECRET_KEY=paste-generated-key-here
APP_ENCRYPTION_KEY=paste-another-generated-key-here
```

### Step 5: Set file permissions

```bash
chmod 600 .env
```

### Step 6: Test

Upload `test_db_connection.php` to your server and visit it to test the connection!

## 📝 Final .env File Should Look Like:

```env
SHOPIFY_API_KEY=a53fcb46618232fcc1aca1bf585e700d
SHOPIFY_API_SECRET=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
SHOPIFY_REDIRECT_URI=https://reportpro.codelocksolutions.com/auth/callback

APP_URL=https://reportpro.codelocksolutions.com
APP_SECRET_KEY=your-generated-secret-key-here
APP_ENCRYPTION_KEY=your-generated-encryption-key-here

DB_HOST=localhost
DB_PORT=3306
DB_NAME=u402017191_report_pro
DB_USER=your_actual_db_username
DB_PASSWORD=your_actual_db_password
```

## 🔒 Security Reminder

- ✅ `.env` file is already in `.gitignore` (won't be committed)
- ✅ Never share your `.env` file publicly
- ✅ Delete `test_db_connection.php` after testing
- ✅ Keep your Shopify API Secret secure

