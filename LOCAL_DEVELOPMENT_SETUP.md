# Local Development Setup Guide

Since your app is running on `localhost`, Shopify can't reach it directly. You need to use a tunneling service like **ngrok** to expose your local server to the internet.

## Quick Setup Steps

### 1. Install ngrok

**Windows:**
```powershell
# Download from https://ngrok.com/download
# Or use Chocolatey
choco install ngrok

# Or use Scoop
scoop install ngrok
```

**Mac:**
```bash
brew install ngrok
```

**Linux:**
```bash
# Download from https://ngrok.com/download
# Or use package manager
```

### 2. Get ngrok Auth Token

1. Sign up at https://ngrok.com (free account works)
2. Get your auth token from dashboard
3. Configure ngrok:
```bash
ngrok config add-authtoken YOUR_AUTH_TOKEN
```

### 3. Start Your Local Server

```bash
npm run dev
```

Your server should be running on `http://localhost:3000`

### 4. Start ngrok Tunnel

Open a **new terminal** and run:

```bash
ngrok http 3000
```

You'll see output like:
```
Forwarding   https://abc123.ngrok-free.app -> http://localhost:3000
```

**Copy the HTTPS URL** (e.g., `https://abc123.ngrok-free.app`)

### 5. Update Your .env File

Update your `.env` file with the ngrok URL:

```env
SHOPIFY_APP_URL=https://abc123.ngrok-free.app
```

**Important**: Every time you restart ngrok, you'll get a new URL. You'll need to:
- Update `.env` file
- Update Shopify app settings

### 6. Configure Shopify App Settings

In your Shopify Partner Dashboard (the page you showed):

1. **App URL**: `https://abc123.ngrok-free.app`
2. **Allowed redirection URL(s)**: 
   - `https://abc123.ngrok-free.app/api/auth/shopify/callback`
   - `https://abc123.ngrok-free.app/api/auth/shopify/callback?shop=*.myshopify.com`

3. **Scopes** (in the text area):
   ```
   read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
   ```

4. **Optional scopes**: Leave empty for now

5. **Webhooks API Version**: `2025-10` (or latest)

### 7. Restart Your Server

After updating `.env`, restart your server:

```bash
# Stop current server (Ctrl+C)
npm run dev
```

## Using ngrok with Fixed Domain (Recommended)

To avoid changing URLs every time, use ngrok's reserved domain (requires paid plan) or use a script to auto-update:

### Option A: ngrok Reserved Domain (Paid)
```bash
ngrok http 3000 --domain=your-reserved-domain.ngrok.app
```

### Option B: Auto-Update Script

Create `start-dev.js`:

```javascript
const { spawn } = require('child_process');
const ngrok = require('ngrok');

(async function() {
  // Start your server
  const server = spawn('npm', ['run', 'dev:server'], { stdio: 'inherit' });
  
  // Start ngrok
  const url = await ngrok.connect(3000);
  console.log(`\n✅ ngrok tunnel: ${url}`);
  console.log(`📝 Update SHOPIFY_APP_URL in .env to: ${url}`);
  console.log(`📝 Update Shopify app settings:\n`);
  console.log(`   App URL: ${url}`);
  console.log(`   Callback URL: ${url}/api/auth/shopify/callback\n`);
  
  process.on('SIGINT', async () => {
    await ngrok.kill();
    server.kill();
    process.exit();
  });
})();
```

## Alternative: Use localtunnel (Free, No Signup)

If you don't want to sign up for ngrok:

```bash
# Install
npm install -g localtunnel

# Start tunnel
lt --port 3000
```

You'll get a URL like: `https://random-name.loca.lt`

## Testing the Setup

1. **Start your server**: `npm run dev`
2. **Start ngrok**: `ngrok http 3000`
3. **Update .env** with ngrok URL
4. **Update Shopify app settings**
5. **Test installation**:
   ```
   https://your-ngrok-url.ngrok-free.app/api/auth/shopify?shop=your-test-store.myshopify.com
   ```

## Troubleshooting

### "Invalid redirect_uri" Error
- Make sure callback URL in Shopify matches exactly: `https://your-url.ngrok-free.app/api/auth/shopify/callback`
- Check for trailing slashes

### ngrok URL Changes Every Time
- Use ngrok reserved domain (paid)
- Or use localtunnel with `--subdomain` flag
- Or create a script to auto-update .env

### "Connection refused"
- Make sure your local server is running on port 3000
- Check ngrok is forwarding to correct port

### CORS Issues
- ngrok handles CORS automatically
- Make sure your server allows the ngrok domain

## Production Deployment

When ready for production:
1. Deploy to a real server (Heroku, AWS, etc.)
2. Update `SHOPIFY_APP_URL` to your production URL
3. Update Shopify app settings with production URLs
4. No need for ngrok in production!

