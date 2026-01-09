# Shopify Partner Dashboard - App Configuration Guide

## Complete Form Fill-Out Instructions

Use this guide to fill out the app creation form in Shopify Partner Dashboard.

---

## 📋 URLs Section

### App URL
```
https://reportpro.codelocksolutions.com
```

### Embed app in Shopify admin
✅ **CHECK THIS BOX** (Required for embedded apps)

This enables your app to load inside the Shopify admin interface.

### Preferences URL (optional)
```
https://reportpro.codelocksolutions.com/settings
```
*Leave blank if you don't have a preferences page yet*

---

## 🔗 Redirect URLs

Enter the following URLs as a **comma-separated list** (NOT on separate lines):

```
https://reportpro.codelocksolutions.com/oauth_callback.php,https://reportpro.codelocksolutions.com/auth/callback
```

**Important Notes:**
- ⚠️ **MUST be comma-separated** (no line breaks, no spaces)
- Both URLs are needed (standalone OAuth script + MVC route)
- Must use HTTPS
- No trailing slashes
- Exact match required
- No spaces after commas

---

## 🔐 Access Section

### Scopes (Required Scopes)

Enter these scopes as a comma-separated list:

```
read_orders,read_products,read_customers,read_inventory,read_transactions,read_analytics
```

**Or click "Select scopes" button and select:**
- ✅ read_orders
- ✅ read_products
- ✅ read_customers
- ✅ read_inventory
- ✅ read_transactions
- ✅ read_analytics

### Optional scopes
*Leave empty* (unless you need additional permissions later)

### Use legacy install flow
❌ **LEAVE UNCHECKED** (Use modern OAuth 2.0 flow)

---

## 📡 Webhooks API Version

Select: **2026-01** (or latest available)

*Note: Your code uses 2024-01, but Shopify may show 2026-01 in the dashboard. Both work, but use the latest available.*

---

## 🛍️ POS Section (Collapsible)

### Embed app in Shopify POS
❌ **LEAVE UNCHECKED** (unless you need POS integration)

---

## 🔌 App Proxy Section (Collapsible)

### Subpath prefix
Select: **apps**

### Subpath
*Leave empty* (unless you need app proxy functionality)

### Proxy URL
*Leave empty* (unless you need app proxy functionality)

---

## ✅ Final Checklist

Before submitting, verify:

- [ ] App URL: `https://reportpro.codelocksolutions.com`
- [ ] Embed app in Shopify admin: ✅ CHECKED
- [ ] Redirect URLs: Both callback URLs entered
- [ ] Required Scopes: All 6 scopes selected
- [ ] Optional scopes: Empty (or add if needed)
- [ ] Use legacy install flow: ❌ UNCHECKED
- [ ] Webhooks API Version: 2026-01 (or latest)
- [ ] POS: ❌ UNCHECKED (unless needed)
- [ ] App Proxy: Empty (unless needed)

---

## 📝 After Creating the App

### Step 1: Get API Credentials

1. Go to **App setup** → **Client credentials**
2. Copy:
   - **API key** (Client ID)
   - **API secret key** (Client secret)

### Step 2: Update Your Config

Edit `config/config.php`:

```php
'shopify' => [
    'api_key' => 'PASTE_YOUR_API_KEY_HERE',
    'api_secret' => 'PASTE_YOUR_API_SECRET_HERE',
    'scopes' => 'read_orders,read_products,read_customers,read_inventory,read_transactions,read_analytics',
    'redirect_uri' => 'https://reportpro.codelocksolutions.com/auth/callback',
    'api_version' => '2024-01',
],
```

### Step 3: Configure Webhooks

Go to **App setup** → **Webhooks** and add:

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

---

## 🧪 Test Installation

After configuration, test the installation:

1. Visit:
   ```
   https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-test-shop.myshopify.com
   ```

2. You should be redirected to Shopify authorization page

3. Click "Install app"

4. You'll be redirected back to your app dashboard

---

## ⚠️ Common Mistakes to Avoid

1. ❌ **Wrong App URL format**
   - ✅ Correct: `https://reportpro.codelocksolutions.com`
   - ❌ Wrong: `http://reportpro.codelocksolutions.com` (must be HTTPS)
   - ❌ Wrong: `https://reportpro.codelocksolutions.com/` (no trailing slash)

2. ❌ **Missing redirect URLs**
   - Must include both callback URLs
   - Must match exactly (case-sensitive)

3. ❌ **Wrong scopes**
   - Must include all required scopes
   - Check spelling (no spaces in scope names)

4. ❌ **Legacy install flow checked**
   - Should be unchecked for modern OAuth 2.0

5. ❌ **Not embedding in admin**
   - Must check "Embed app in Shopify admin" for embedded apps

---

## 📞 Need Help?

If you encounter issues:

1. Verify SSL certificate is valid for your domain
2. Check that all URLs are accessible
3. Review error logs: `storage/oauth.log`
4. Ensure API credentials are correctly set in config

---

## 🎯 Quick Reference

**App URL:**
```
https://reportpro.codelocksolutions.com
```

**Redirect URLs:**
```
https://reportpro.codelocksolutions.com/oauth_callback.php
https://reportpro.codelocksolutions.com/auth/callback
```

**Required Scopes:**
```
read_orders,read_products,read_customers,read_inventory,read_transactions,read_analytics
```

**Webhook URLs:**
```
https://reportpro.codelocksolutions.com/webhooks/app/uninstalled
https://reportpro.codelocksolutions.com/webhooks/customers/data_request
https://reportpro.codelocksolutions.com/webhooks/customers/redact
https://reportpro.codelocksolutions.com/webhooks/shop/redact
```

