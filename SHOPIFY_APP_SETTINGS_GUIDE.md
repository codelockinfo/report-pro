# Shopify App Settings Configuration Guide

Based on the Shopify Partner Dashboard settings page you're viewing, here's exactly what to fill in:

## 📋 Settings Configuration

### 1. App name
```
ReportPro - Easy Report
```
(You already have this - looks good!)

### 2. URLs Section

#### App URL
```
https://your-ngrok-url.ngrok-free.app
```
**Important**: Replace `your-ngrok-url.ngrok-free.app` with your actual ngrok URL

#### ✅ Embed app in Shopify admin
**Check this box** (you already have it checked - good!)

#### Preferences URL (optional)
Leave this **empty** for now

### 3. Webhooks API Version
Select: **2025-10** (or the latest version available)

### 4. Access Section

#### Required Scopes
In the **"Scopes"** text area (the large empty box), paste exactly:

```
read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
```

**Note**: If you see "Request access" link, click it to request permission for:
- `read_inventory`
- `read_locations` 
- `write_webhooks`

These may require approval from Shopify.

#### Optional scopes
Leave the **"Optional scopes"** text area **empty** for now

## 🔗 Getting Your ngrok URL

### Step 1: Install ngrok
```powershell
# Download from https://ngrok.com/download
# Or use Chocolatey
choco install ngrok
```

### Step 2: Get Auth Token
1. Sign up at https://ngrok.com (free)
2. Get auth token from dashboard
3. Run: `ngrok config add-authtoken YOUR_TOKEN`

### Step 3: Start Tunnel
```powershell
# In a new terminal
ngrok http 3000
```

You'll see:
```
Forwarding   https://abc123.ngrok-free.app -> http://localhost:3000
```

**Copy the HTTPS URL** (e.g., `https://abc123.ngrok-free.app`)

### Step 4: Update Settings
1. **App URL**: `https://abc123.ngrok-free.app`
2. **Allowed redirection URL(s)**: 
   - Go to your app's "App setup" → "URLs"
   - Add: `https://abc123.ngrok-free.app/api/auth/shopify/callback`

## 📝 Complete Settings Checklist

- [ ] App name: `ReportPro - Easy Report`
- [ ] App URL: `https://your-ngrok-url.ngrok-free.app`
- [ ] ✅ Embed app in Shopify admin: **Checked**
- [ ] Preferences URL: **Empty**
- [ ] Webhooks API Version: `2025-10`
- [ ] Required Scopes: All 7 scopes pasted
- [ ] Optional scopes: **Empty**
- [ ] Allowed redirection URL: Added callback URL

## ⚠️ Important Notes

1. **ngrok URL changes** every time you restart (free plan)
   - You'll need to update Shopify settings each time
   - Consider ngrok paid plan for fixed domain

2. **Scope Permissions**
   - Some scopes may need approval
   - Click "Request access" if needed
   - Wait for approval before testing

3. **Callback URL Format**
   - Must be: `https://your-url/api/auth/shopify/callback`
   - No trailing slash
   - Must match exactly

## 🧪 Testing After Configuration

1. Save all settings in Shopify
2. Start your local server: `npm run dev`
3. Start ngrok: `ngrok http 3000`
4. Test installation:
   ```
   https://your-ngrok-url.ngrok-free.app/api/auth/shopify?shop=your-test-store.myshopify.com
   ```

## 🚀 Quick Command Reference

```powershell
# Terminal 1: Start app
npm run dev

# Terminal 2: Start ngrok
ngrok http 3000

# Copy the HTTPS URL and update:
# 1. .env file: SHOPIFY_APP_URL=https://...
# 2. Shopify app settings: App URL
# 3. Shopify app settings: Callback URL
```

