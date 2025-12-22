# Fix: Missing Frontend Files on Server

## Problem
Your `public_html` directory has backend files but is missing the `frontend/dist` directory. The server can't serve the frontend without it.

## Solution: Deploy Frontend Files

### Option 1: Build on Server (Recommended)

SSH into your server and run:

```bash
# Navigate to your project directory
cd public_html

# Install frontend dependencies (if not already installed)
cd frontend
npm install
cd ..

# Build the frontend
npm run build:client
# OR
cd frontend && npm run build && cd ..
```

This will create `frontend/dist/` directory with all frontend files.

### Option 2: Upload Pre-built Frontend

If you've already built locally:

1. **On your local machine**, ensure frontend is built:
   ```bash
   cd frontend
   npm run build
   ```

2. **Upload the `frontend/dist` folder** to your server:
   - Via File Manager: Upload `frontend/dist` folder to `public_html/frontend/`
   - Via SFTP: Upload the entire `frontend/dist` directory
   - Via Git: Make sure `frontend/dist` is committed (not recommended, but works)

### Option 3: Check Current Structure

First, verify what's actually on your server:

```bash
# SSH into server
cd public_html
ls -la
ls -la frontend/  # Check if frontend directory exists
```

## Expected Structure After Fix

Your `public_html` should have:

```
public_html/
├── server.js              ✅ (you have this)
├── server.d.ts            ✅ (you have this)
├── controllers/           ✅ (you have this)
├── database/              ✅ (you have this)
├── middleware/            ✅ (you have this)
├── routes/                ✅ (you have this)
├── services/              ✅ (you have this)
├── frontend/              ❌ MISSING or incomplete
│   └── dist/              ❌ MISSING - THIS IS THE PROBLEM
│       ├── index.html     ❌ MISSING
│       └── assets/        ❌ MISSING
│           ├── index-*.js
│           └── index-*.css
├── .env                   (should exist)
└── package.json           (should exist)
```

## Quick Fix Commands

Run these on your server:

```bash
cd public_html

# 1. Check if frontend directory exists
ls -la frontend/

# 2. If frontend exists but dist is missing, build it:
cd frontend
npm install
npm run build
cd ..

# 3. Verify dist was created
ls -la frontend/dist/

# 4. Restart your app
pm2 restart report-pro

# 5. Check logs
pm2 logs report-pro
```

## Verify It Works

After deploying frontend:

1. **Test direct access:**
   ```
   https://your-domain.com
   ```
   Should show your React app (not 403)

2. **Test diagnostic endpoint:**
   ```
   https://your-domain.com/api/diagnostic
   ```
   Should return JSON

3. **Check server logs:**
   ```bash
   pm2 logs report-pro
   ```
   Should NOT show "index.html not found" errors

## If Frontend Directory Doesn't Exist

If there's no `frontend/` directory at all on the server:

1. **Upload entire frontend folder** from your local machine
2. **OR clone/pull from Git** (if frontend is in repo)
3. **Then build it** on the server

## Important Notes

- The `frontend/dist` folder contains the **built/compiled** frontend
- This is different from `frontend/src` which has source code
- You need BOTH on the server:
  - `frontend/src/` - for future builds (optional, can delete after build)
  - `frontend/dist/` - **REQUIRED** - this is what the server serves

## After Fixing

Once `frontend/dist` exists, your app should work! The 403 error should be resolved.

