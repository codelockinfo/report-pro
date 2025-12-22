# Quick Local Development Setup

Your app runs on `localhost`, but Shopify needs a public URL. Use **ngrok** to create a tunnel.

## 🚀 Quick Start (3 Steps)

### Step 1: Install ngrok

**Windows (PowerShell as Admin):**
```powershell
# Option 1: Using Chocolatey
choco install ngrok

# Option 2: Using Scoop
scoop install ngrok

# Option 3: Download from https://ngrok.com/download
```

**Mac:**
```bash
brew install ngrok
```

### Step 2: Get ngrok Auth Token (Free)

1. Sign up at https://ngrok.com (free account)
2. Copy your auth token from dashboard
3. Configure:
```bash
ngrok config add-authtoken YOUR_TOKEN_HERE
```

### Step 3: Start Development with Tunnel

**Option A: Manual (Simple)**
```bash
# Terminal 1: Start your app
npm run dev

# Terminal 2: Start ngrok
ngrok http 3000
```

Copy the HTTPS URL (e.g., `https://abc123.ngrok-free.app`)

**Option B: Automatic (Recommended)**
```bash
# Install ngrok package
npm install

# Start with auto-tunnel
npm run dev:tunnel
```

This will:
- Start your server
- Start ngrok tunnel
- Auto-update `.env` file
- Show you the URLs to use

## 📝 Configure Shopify App Settings

In your Shopify Partner Dashboard (the settings page you showed):

### 1. App URL
```
https://your-ngrok-url.ngrok-free.app
```

### 2. Allowed redirection URL(s)
Add these URLs:
```
https://your-ngrok-url.ngrok-free.app/api/auth/shopify/callback
```

### 3. Scopes (Required)
In the "Scopes" text area, paste:
```
read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
```

### 4. Optional scopes
Leave empty for now.

### 5. Webhooks API Version
Select: `2025-10` (or latest available)

## ✅ Test Installation

1. Visit this URL (replace with your ngrok URL):
```
https://your-ngrok-url.ngrok-free.app/api/auth/shopify?shop=your-test-store.myshopify.com
```

2. Complete OAuth flow
3. Check database: `SELECT * FROM shops;`

## ⚠️ Important Notes

- **ngrok URL changes** every time you restart ngrok (free plan)
- **Update Shopify settings** each time you get a new URL
- **Update `.env`** file with new URL
- **Restart server** after updating `.env`

## 🔧 Alternative: Use Fixed Domain

To avoid changing URLs, use ngrok reserved domain (paid) or localtunnel:

```bash
# Install localtunnel (free, no signup)
npm install -g localtunnel

# Start with fixed subdomain
lt --port 3000 --subdomain your-app-name
```

## 📚 More Details

See `LOCAL_DEVELOPMENT_SETUP.md` for complete guide.

