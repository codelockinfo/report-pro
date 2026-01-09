# 🔒 Security Notes - API Credentials

## ⚠️ Important Security Information

Your Shopify API credentials are now stored in `config/config.php`.

### Current Configuration

- **API Key**: `a53fcb46618232fcc1aca1bf585e700d`
- **API Secret**: `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`

## 🛡️ Security Best Practices

### 1. File Permissions

Set proper file permissions on config file:

```bash
chmod 640 config/config.php
chown www-data:www-data config/config.php
```

This ensures:
- Owner (you) can read/write
- Group (web server) can read
- Others cannot access

### 2. Version Control

⚠️ **IMPORTANT**: If using Git:

- ✅ `.gitignore` already includes `.env` files
- ⚠️ `config/config.php` is currently tracked
- 🔒 **DO NOT commit credentials to public repositories**

**Options:**

**Option A: Use Environment Variables (Recommended)**
```bash
# Set environment variables
export SHOPIFY_API_KEY="a53fcb46618232fcc1aca1bf585e700d"
export SHOPIFY_API_SECRET="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"
```

Then update `config/config.php` to use:
```php
'api_key' => getenv('SHOPIFY_API_KEY'),
'api_secret' => getenv('SHOPIFY_API_SECRET'),
```

**Option B: Create .env File**
```bash
# Create .env file (already in .gitignore)
SHOPIFY_API_KEY=a53fcb46618232fcc1aca1bf585e700d
SHOPIFY_API_SECRET=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
```

**Option C: Separate Config File**
- Create `config/config.production.php` (add to .gitignore)
- Load it conditionally based on environment

### 3. Server Security

- ✅ Use HTTPS only (SSL certificate required)
- ✅ Keep PHP files outside web root if possible
- ✅ Regular security updates
- ✅ Firewall configuration
- ✅ Limit file access permissions

### 4. Access Control

- ✅ Restrict SSH access
- ✅ Use strong passwords
- ✅ Enable two-factor authentication
- ✅ Regular security audits

## 🔐 If Credentials Are Compromised

If your API credentials are exposed:

1. **Immediately regenerate** in Shopify Partner Dashboard:
   - Go to App setup → Client credentials
   - Click "Regenerate" for API secret
   - Update config file with new credentials

2. **Review access logs**:
   - Check `storage/oauth.log`
   - Review Shopify app access logs
   - Check for unauthorized API calls

3. **Revoke and regenerate**:
   - Regenerate API secret
   - Update all instances of credentials
   - Test OAuth flow again

## 📋 Security Checklist

Before going to production:

- [ ] Config file has proper permissions (640)
- [ ] Credentials not in version control
- [ ] Using environment variables or secure config
- [ ] SSL certificate installed and valid
- [ ] File permissions set correctly
- [ ] Error logging enabled (but not exposing credentials)
- [ ] Regular security updates scheduled
- [ ] Backup strategy in place

## 🚨 Emergency Contacts

If you suspect a security breach:

1. Regenerate API credentials immediately
2. Review all access logs
3. Check for unauthorized installations
4. Contact Shopify support if needed

## 📝 Current Status

✅ Credentials configured in `config/config.php`
✅ `.gitignore` includes sensitive files
⚠️ Ensure config file is not in public Git repository
⚠️ Set proper file permissions before production

