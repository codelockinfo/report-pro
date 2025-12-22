# 🚀 Start Here - Local Development Setup

Follow these steps to test your app locally before deploying to Hostinger.

## Step-by-Step Instructions

### 1️⃣ Install ngrok (One-time setup)

**Option A: Download (Easiest)**
1. Go to https://ngrok.com/download
2. Download Windows version
3. Extract `ngrok.exe` to a folder (e.g., `C:\ngrok`)
4. Add that folder to your PATH, OR just use full path

**Option B: Using Chocolatey (if you have it)**
```powershell
choco install ngrok
```

**Option C: Using Scoop (if you have it)**
```powershell
scoop install ngrok
```

### 2️⃣ Get ngrok Auth Token (Free)

1. Sign up at https://ngrok.com (free account)
2. Login and go to: https://dashboard.ngrok.com/get-started/your-authtoken
3. Copy your authtoken
4. Run this command:
```powershell
ngrok config add-authtoken YOUR_TOKEN_HERE
```

### 3️⃣ Start Your App

```powershell
# Make sure MySQL and Redis are running first
npm run dev
```

Your app should be running on `http://localhost:3000`

### 4️⃣ Start ngrok (New Terminal)

Open a **NEW PowerShell window** and run:

```powershell
ngrok http 3000
```

You'll see something like:
```
Forwarding   https://abc123.ngrok-free.app -> http://localhost:3000
```

**📋 Copy the HTTPS URL** (e.g., `https://abc123.ngrok-free.app`)

### 5️⃣ Update .env File

Edit your `.env` file and change this line:

```env
SHOPIFY_APP_URL=https://abc123.ngrok-free.app
```

Replace `abc123.ngrok-free.app` with your actual ngrok URL.

### 6️⃣ Update Shopify App Settings

Go to your Shopify Partner Dashboard and update:

1. **App URL**: 
   ```
   https://abc123.ngrok-free.app
   ```

2. **Allowed redirection URL(s)**:
   - Find "App setup" → "URLs" section
   - Add: `https://abc123.ngrok-free.app/api/auth/shopify/callback`

3. **Required Scopes** (in the text area):
   ```
   read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
   ```

### 7️⃣ Restart Your Server

After updating `.env`, restart:

```powershell
# Stop server (Ctrl+C)
npm run dev
```

### 8️⃣ Test Installation

Visit this URL (replace with your ngrok URL and test store):
```
https://abc123.ngrok-free.app/api/auth/shopify?shop=your-test-store.myshopify.com
```

## ✅ Quick Commands Reference

```powershell
# Terminal 1: Start your app
npm run dev

# Terminal 2: Start ngrok
ngrok http 3000

# After getting ngrok URL:
# 1. Update .env: SHOPIFY_APP_URL=https://...
# 2. Update Shopify settings
# 3. Restart app (Ctrl+C then npm run dev)
```

## ⚠️ Important Notes

- **ngrok URL changes** every time you restart ngrok (free plan)
- **Update both** `.env` and Shopify settings each time
- **Keep ngrok running** while testing
- **Use ngrok web interface**: `http://127.0.0.1:4040` to see requests

## 🎯 When Ready for Hostinger

1. Deploy code to Hostinger
2. Update `.env` on server: `SHOPIFY_APP_URL=https://your-hostinger-domain.com`
3. Update Shopify settings with Hostinger URL
4. Done! No more ngrok needed.

## 🆘 Troubleshooting

**"ngrok not found"**
- Make sure ngrok is in your PATH
- Or use full path: `C:\path\to\ngrok.exe http 3000`

**"Invalid redirect_uri"**
- Check callback URL matches exactly in Shopify
- No trailing slashes

**"Connection refused"**
- Make sure app is running on port 3000
- Check ngrok is forwarding correctly

---

**Need help?** Check `LOCAL_DEVELOPMENT.md` for detailed guide.

