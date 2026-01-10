# 🔐 Setting Up API Credentials

## ⚠️ Security First

**Never commit API credentials to Git!** GitHub's push protection will block commits containing secrets.

## ✅ Recommended: Use Environment Variables

### Option 1: .env File (Easiest)

1. **Copy the example file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` file** and add your credentials:
   ```bash
   SHOPIFY_API_KEY=your_api_key_here
   SHOPIFY_API_SECRET=your_api_secret_here
   ```

3. **Load environment variables** in your PHP application:
   
   You may need to add this to `index.php` or create a loader:
   ```php
   // Load .env file
   if (file_exists(__DIR__ . '/.env')) {
       $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
       foreach ($lines as $line) {
           if (strpos(trim($line), '#') === 0) continue; // Skip comments
           list($name, $value) = explode('=', $line, 2);
           putenv(trim($name) . '=' . trim($value));
       }
   }
   ```

### Option 2: Server Environment Variables

Set environment variables on your server:

**Apache (.htaccess or httpd.conf):**
```apache
SetEnv SHOPIFY_API_KEY "your_api_key_here"
SetEnv SHOPIFY_API_SECRET "your_api_secret_here"
```

**Nginx:**
```nginx
fastcgi_param SHOPIFY_API_KEY "your_api_key_here";
fastcgi_param SHOPIFY_API_SECRET "your_api_secret_here";
```

**cPanel/Shared Hosting:**
- Use the hosting panel's environment variable settings
- Or create `.env` file in the root directory

### Option 3: Direct in config.php (NOT Recommended for Git)

If you're not using Git or it's a private repository, you can temporarily set them directly in `config/config.php`:

```php
'api_key' => 'your_api_key_here',
'api_secret' => 'your_api_secret_here',
```

**⚠️ Warning**: This will be blocked by GitHub push protection if you try to commit.

## 🔧 Your Credentials

Get these from Shopify Partner Dashboard:
- Go to App setup → Client credentials
- Copy API key and API secret
- Store in `.env` file (never commit to Git)

## ✅ Verification

After setting up credentials, test:

1. Visit: `https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-test-shop.myshopify.com`
2. Should redirect to Shopify authorization page
3. If you see "API credentials not configured", environment variables aren't loading

## 🛡️ Security Checklist

- [ ] Credentials set as environment variables
- [ ] `.env` file created (not committed to Git)
- [ ] `.gitignore` includes `.env`
- [ ] No credentials in `config/config.php`
- [ ] No credentials in documentation files
- [ ] File permissions set correctly (640 for config files)

## 🚨 If You Already Committed Credentials

If credentials were already committed:

1. **Remove from Git history** (if not pushed):
   ```bash
   git reset HEAD~1
   # Then remove credentials and recommit
   ```

2. **If already pushed**, you need to:
   - Regenerate API secret in Shopify Partner Dashboard
   - Remove credentials from all files
   - Force push (if repository allows) or create new commit
   - Consider the old secret compromised

3. **Regenerate credentials** in Shopify:
   - Go to Partner Dashboard → App setup → Client credentials
   - Click "Regenerate" for API secret
   - Update your environment variables with new secret

## 📝 Next Steps

1. ✅ Set up `.env` file with credentials
2. ✅ Test OAuth installation
3. ✅ Verify no credentials in Git
4. ✅ Set proper file permissions

