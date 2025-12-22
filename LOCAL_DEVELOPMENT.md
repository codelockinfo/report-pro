# Local Development Setup (Before Hostinger Deployment)

Since you have a Hostinger server, you can develop and test locally first, then deploy when ready.

## 🚀 Quick Local Setup

### Step 1: Install ngrok

**Windows (PowerShell):**
```powershell
# Option 1: Download from https://ngrok.com/download
# Extract and add to PATH

# Option 2: Using Chocolatey (if installed)
choco install ngrok

# Option 3: Using Scoop (if installed)
scoop install ngrok
```

### Step 2: Get ngrok Auth Token (Free)

1. Go to https://ngrok.com and sign up (free account)
2. Go to Dashboard → Your Authtoken
3. Copy your token
4. Run:
```powershell
ngrok config add-authtoken YOUR_TOKEN_HERE
```

### Step 3: Start Your Local App

```powershell
# Make sure MySQL and Redis are running
# Start your app
npm run dev
```

Your app should be running on `http://localhost:3000`

### Step 4: Start ngrok Tunnel

Open a **NEW terminal window** and run:

```powershell
ngrok http 3000
```

You'll see output like:
```
Session Status                online
Account                       Your Name (Plan: Free)
Version                       3.x.x
Region                        United States (us)
Latency                       -
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123.ngrok-free.app -> http://localhost:3000

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

**Copy the HTTPS URL**: `https://abc123.ngrok-free.app` (yours will be different)

### Step 5: Update .env File

Edit your `.env` file and update:

```env
SHOPIFY_APP_URL=https://abc123.ngrok-free.app
```

Replace `abc123.ngrok-free.app` with your actual ngrok URL.

### Step 6: Configure Shopify App Settings

In your Shopify Partner Dashboard:

1. **App URL**: 
   ```
   https://abc123.ngrok-free.app
   ```

2. **Allowed redirection URL(s)**:
   - Go to "App setup" → "URLs" section
   - Add: `https://abc123.ngrok-free.app/api/auth/shopify/callback`

3. **Required Scopes** (paste in the text area):
   ```
   read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
   ```

4. **Webhooks API Version**: `2025-10`

5. **Optional scopes**: Leave empty

### Step 7: Restart Your Server

After updating `.env`, restart your server:

```powershell
# Stop current server (Ctrl+C)
npm run dev
```

## ✅ Test Your Local Setup

1. Visit this URL (replace with your ngrok URL and test store):
```
https://abc123.ngrok-free.app/api/auth/shopify?shop=your-test-store.myshopify.com
```

2. Complete OAuth flow
3. Check database:
```sql
SELECT * FROM shops;
```

## ⚠️ Important Notes

### ngrok URL Changes
- **Free ngrok**: URL changes every time you restart
- **Solution**: Update `.env` and Shopify settings each time
- **Alternative**: Use ngrok paid plan for fixed domain

### When ngrok Restarts
1. Get new URL from ngrok
2. Update `.env` file: `SHOPIFY_APP_URL=...`
3. Update Shopify app settings (App URL and Callback URL)
4. Restart your server

## 🔄 Workflow

**Daily Development:**
```powershell
# Terminal 1: Start app
npm run dev

# Terminal 2: Start ngrok
ngrok http 3000

# Copy new URL → Update .env → Update Shopify → Restart app
```

## 📦 When Ready for Hostinger

When you're ready to deploy to Hostinger:

1. **Deploy your code** to Hostinger
2. **Update .env** on server:
   ```env
   SHOPIFY_APP_URL=https://your-hostinger-domain.com
   ```
3. **Update Shopify app settings**:
   - App URL: `https://your-hostinger-domain.com`
   - Callback URL: `https://your-hostinger-domain.com/api/auth/shopify/callback`
4. **No more ngrok needed!** 🎉

## 🛠️ Troubleshooting

### "Invalid redirect_uri"
- Check callback URL matches exactly in Shopify
- No trailing slashes
- Must be HTTPS

### "Connection refused"
- Make sure app is running on port 3000
- Check ngrok is forwarding to correct port

### ngrok URL keeps changing
- Use ngrok reserved domain (paid)
- Or use localtunnel with fixed subdomain:
  ```powershell
  npm install -g localtunnel
  lt --port 3000 --subdomain your-app-name
  ```

## 💡 Pro Tips

1. **Keep ngrok running** in a separate terminal
2. **Bookmark** your ngrok web interface: `http://127.0.0.1:4040`
3. **Use ngrok inspector** to see all requests
4. **Test thoroughly locally** before deploying to Hostinger

