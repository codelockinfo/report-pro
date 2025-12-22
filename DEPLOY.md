# Quick Deployment Guide - Hostinger

## 🎯 Your Plan (Perfect!)

1. ✅ Complete code → 2. ✅ Push to Git → 3. ✅ Deploy to Hostinger → 4. ✅ Add URL to Shopify

## 📦 Step 1: Finalize Code

Everything is ready! Your code includes:
- ✅ Backend API (Node.js/TypeScript/Express)
- ✅ Frontend (React/Polaris)
- ✅ Database models (MySQL)
- ✅ OAuth flow (Shopify)
- ✅ All 4 menus (Reports, Explore, Schedule, Chart Analysis, Settings)
- ✅ Background jobs (BullMQ)
- ✅ Redis caching

## 🚀 Step 2: Push to Git

```bash
# Add all files
git add .

# Commit
git commit -m "Ready for Hostinger deployment - Complete Shopify Reports App"

# Push to your repository
git push origin main
```

## 📥 Step 3: Deploy to Hostinger

### Quick Steps:

1. **SSH into Hostinger:**
```bash
ssh your-username@your-hostinger-ip
```

2. **Navigate to your domain directory:**
```bash
cd public_html
# OR for subdomain:
cd subdomains/your-subdomain/public_html
```

3. **Clone your repository:**
```bash
git clone https://github.com/your-username/report-pro.git .
```

4. **Install dependencies:**
```bash
npm install --production
cd frontend && npm install --production && cd ..
```

5. **Build the app:**
```bash
npm run build
```

6. **Create `.env` file:**
```bash
nano .env
```
Paste your environment variables (see `HOSTINGER_DEPLOYMENT.md` for template)

7. **Start with PM2:**
```bash
npm install -g pm2
pm2 start dist/server.js --name report-pro
pm2 save
pm2 startup
```

8. **Configure web server** (Nginx/Apache) - See `HOSTINGER_DEPLOYMENT.md`

## 🔗 Step 4: Configure Shopify App

Once deployed, update Shopify Partner Dashboard:

1. **App URL:**
   ```
   https://your-domain.com
   ```

2. **Allowed redirection URL(s):**
   ```
   https://your-domain.com/api/auth/shopify/callback
   ```

3. **Required Scopes:**
   ```
   read_orders,read_transactions,read_products,read_customers,read_inventory,read_locations,write_webhooks
   ```

4. **Save settings**

## ✅ Test

Visit: `https://your-domain.com/health`

Should return: `{"status":"ok","timestamp":"..."}`

## 📚 Detailed Guides

- **`HOSTINGER_DEPLOYMENT.md`** - Complete deployment guide
- **`DEPLOYMENT_CHECKLIST.md`** - Pre-deployment checklist
- **`ecosystem.config.js`** - PM2 configuration file

## 🆘 Need Help?

1. Check `HOSTINGER_DEPLOYMENT.md` for detailed steps
2. Check PM2 logs: `pm2 logs report-pro`
3. Verify environment variables
4. Test health endpoint first

---

**You're all set! Good luck with deployment! 🚀**

