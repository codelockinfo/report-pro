# Deployment Checklist - Hostinger

Use this checklist to ensure everything is ready before deploying.

## ✅ Pre-Deployment

### Code Preparation
- [ ] All code is committed to Git
- [ ] `.gitignore` is properly configured
- [ ] No sensitive data in code (use `.env`)
- [ ] All dependencies in `package.json`
- [ ] Frontend dependencies in `frontend/package.json`
- [ ] TypeScript compiles without errors
- [ ] No console errors in browser

### Environment Variables
- [ ] `.env.example` file exists
- [ ] All required variables documented
- [ ] Production values ready (not localhost)

### Database
- [ ] MySQL database created on Hostinger
- [ ] Database credentials noted
- [ ] Database name, user, password ready

### Redis (Optional)
- [ ] Redis access configured (if available)
- [ ] Redis credentials ready

### Shopify App
- [ ] Shopify Partner account active
- [ ] App created in Partner Dashboard
- [ ] API Key and Secret ready
- [ ] Scopes list ready

## 🚀 Deployment Steps

### Step 1: Git Push
- [ ] Code pushed to repository
- [ ] Repository is accessible from Hostinger

### Step 2: Server Setup
- [ ] SSH access to Hostinger
- [ ] Node.js installed (18+)
- [ ] npm installed
- [ ] PM2 installed (for process management)

### Step 3: Code Deployment
- [ ] Repository cloned/pulled on server
- [ ] Dependencies installed (`npm install`)
- [ ] Frontend dependencies installed
- [ ] Application built (`npm run build`)

### Step 4: Configuration
- [ ] `.env` file created on server
- [ ] All environment variables set
- [ ] Database credentials configured
- [ ] Redis configured (if using)
- [ ] Shopify credentials configured
- [ ] Domain URL set in `SHOPIFY_APP_URL`

### Step 5: Database
- [ ] Database created
- [ ] Database user created
- [ ] Permissions granted
- [ ] Tables created (auto-migrate on start)

### Step 6: Application Start
- [ ] App started with PM2
- [ ] PM2 auto-start configured
- [ ] Health check works (`/health`)

### Step 7: Web Server
- [ ] Nginx/Apache configured
- [ ] Proxy to Node.js app set up
- [ ] SSL certificate installed
- [ ] HTTPS enabled

### Step 8: Shopify Configuration
- [ ] App URL updated in Shopify
- [ ] Callback URL added
- [ ] Scopes configured
- [ ] Settings saved

## 🧪 Testing

### Basic Tests
- [ ] Health endpoint: `https://your-domain.com/health`
- [ ] Frontend loads: `https://your-domain.com`
- [ ] API responds: `https://your-domain.com/api/reports`

### OAuth Flow
- [ ] OAuth initiation works
- [ ] Callback URL works
- [ ] Shop data saved to database
- [ ] App installation completes

### Functionality
- [ ] Reports page loads
- [ ] Schedule page loads
- [ ] Chart Analysis works
- [ ] Settings page works

## 📋 Post-Deployment

### Monitoring
- [ ] PM2 monitoring set up
- [ ] Logs accessible
- [ ] Error tracking (optional)

### Security
- [ ] `.env` file not in Git
- [ ] SSL certificate active
- [ ] Strong JWT secret set
- [ ] Database credentials secure

### Documentation
- [ ] Deployment notes saved
- [ ] Credentials stored securely
- [ ] Team notified (if applicable)

## 🔄 Update Process

When updating:
- [ ] Pull latest code
- [ ] Install new dependencies
- [ ] Rebuild application
- [ ] Restart with PM2
- [ ] Test changes
- [ ] Monitor logs

---

**Check each item before moving to production!**

