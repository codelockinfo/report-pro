# Deploying Shopify Sidebar Navigation

## What Changed

✅ Added `[app_home]` section to `shopify.app.toml`
✅ Added navigation links configuration
✅ This will move the menu from the top bar to the left sidebar

## Current Status

❌ Configuration is in `shopify.app.toml` but NOT deployed to Shopify yet
❌ Navigation still appears in top bar (red box in screenshot)
✅ Once deployed, navigation will appear in left sidebar

## Deployment Steps

### Option 1: Using Shopify CLI (Recommended)

1. **Install Shopify CLI** (if not already installed):
   ```bash
   npm install -g @shopify/cli @shopify/app
   ```

2. **Link your app** (one-time setup):
   ```bash
   cd c:\xampp\htdocs\report-pro
   shopify app config link
   ```
   - Select your app from the list or enter the client ID: `a53fcb46618232fcc1aca1bf585e700d`

3. **Deploy the configuration**:
   ```bash
   shopify app deploy
   ```
   - This will push the `shopify.app.toml` configuration to Shopify
   - The navigation menu will update automatically

4. **Verify**:
   - Open your app in Shopify Admin
   - The navigation should now appear in the LEFT SIDEBAR
   - Not in the top horizontal bar

### Option 2: Manual Configuration in Partner Dashboard

If you can't use Shopify CLI, you can configure this manually:

1. Go to [Shopify Partner Dashboard](https://partners.shopify.com/)
2. Navigate to **Apps** → **ReportPro - Easy Report**
3. Go to **Configuration** → **App setup**
4. Find **App home** section
5. Set **App home URL**: `https://reportpro.codelocksolutions.com/dashboard`
6. Add navigation links:
   - Dashboard: `https://reportpro.codelocksolutions.com/dashboard`
   - Reports: `https://reportpro.codelocksolutions.com/reports`
   - Chart Analysis: `https://reportpro.codelocksolutions.com/chart-analysis`
   - Schedule: `https://reportpro.codelocksolutions.com/schedule`
   - Settings: `https://reportpro.codelocksolutions.com/settings`
7. **Save** the configuration

## Important Notes

### Why is the menu in the top bar now?
The menu appears in the top bar because:
1. ✅ Your `ui-nav-menu` HTML is working correctly
2. ❌ But the `shopify.app.toml` configuration hasn't been deployed yet
3. ❌ Shopify doesn't know to render it in the sidebar

### What happens after deployment?
After deploying the configuration:
1. ✅ Shopify reads the `[app_home]` section
2. ✅ Recognizes your app should have sidebar navigation
3. ✅ Moves the menu from top bar → left sidebar
4. ✅ The `ui-nav-menu` HTML and TOML config work together

### Do I need both ui-nav-menu AND shopify.app.toml?
**YES!** Both are required:
- `ui-nav-menu` in HTML → Tells Shopify what links to show
- `[app_home]` in TOML → Tells Shopify WHERE to show them (sidebar vs top bar)

## Verification Checklist

After deployment, verify:
- [ ] Navigation appears in LEFT SIDEBAR (not top bar)
- [ ] All 5 menu items are visible (Dashboard, Reports, Chart Analysis, Schedule, Settings)
- [ ] Clicking menu items navigates correctly
- [ ] Active state highlights the current page
- [ ] Works on both desktop and mobile

## Troubleshooting

**Problem**: Menu still in top bar after deployment
**Solution**: 
- Clear browser cache
- Reinstall the app in a test store
- Wait 5-10 minutes for Shopify to propagate changes

**Problem**: Shopify CLI not found
**Solution**: Install it first:
```bash
npm install -g @shopify/cli @shopify/app
```

**Problem**: Can't link the app
**Solution**: Make sure you're logged in:
```bash
shopify auth login
```

## Next Steps

1. ✅ Commit the `shopify.app.toml` changes
2. ✅ Deploy using Shopify CLI or Partner Dashboard
3. ✅ Test in Shopify Admin
4. ✅ Verify sidebar navigation appears correctly

---

**Status**: Configuration ready, awaiting deployment
**Expected Result**: Navigation moves from top bar → left sidebar
