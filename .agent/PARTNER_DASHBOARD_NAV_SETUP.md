# Configure Shopify Sidebar Navigation (Partner Dashboard Method)

## Important Discovery

❌ **`app_home` in `shopify.app.toml` is NOT supported for PHP apps**
✅ **Sidebar navigation MUST be configured through Partner Dashboard UI**

The `ui-nav-menu` HTML element you added is correct, but to make it appear in the SIDEBAR (not top bar), you need to configure it in the Shopify Partner Dashboard.

## Step-by-Step Guide

### Step 1: Access Partner Dashboard

1. Go to [Shopify Partner Dashboard](https://partners.shopify.com/)
2. Log in with your partner account
3. Click on **Apps** in the left sidebar
4. Find and click **ReportPro - Easy Report**

### Step 2: Navigate to App Setup

1. In your app's dashboard, click **Configuration** in the left menu
2. Click **App setup**
3. Scroll down to find the **Navigation** or **App navigation** section

### Step 3: Configure Navigation Menu

Look for one of these sections (varies by Shopify interface version):

#### Option A: "App navigation" Section
If you see an "App navigation" section:

1. **Enable navigation menu**: Toggle ON
2. **Add navigation items** (in order):
   - **Dashboard**
     - Label: `Dashboard`
     - URL: `https://reportpro.codelocksolutions.com/dashboard`
   
   - **Reports**
     - Label: `Reports`
     - URL: `https://reportpro.codelocksolutions.com/reports`
   
   - **Chart Analysis**
     - Label: `Chart Analysis`
     - URL: `https://reportpro.codelocksolutions.com/chart-analysis`
   
   - **Schedule**
     - Label: `Schedule`
     - URL: `https://reportpro.codelocksolutions.com/schedule`
   
   - **Settings**
     - Label: `Settings`
     - URL: `https://reportpro.codelocksolutions.com/settings`

3. Click **Save**

#### Option B: "Embedded app" Section
If you see an "Embedded app" section:

1. Ensure **Embedded app** is set to **Enabled** (should already be)
2. Look for **Navigation links** or **App navigation links**
3. Click **Add link** for each menu item
4. Add the same 5 links as above
5. Click **Save**

### Step 4: Alternative - Use App Extensions

If the above options aren't available, you may need to create an **App Extension**:

1. In Partner Dashboard, go to your app
2. Click **Extensions** → **Create extension**
3. Select **App navigation** or **Navigation menu**
4. Configure the navigation links
5. Deploy the extension

### Step 5: Verify Configuration

After saving:

1. **Wait 5-10 minutes** for Shopify to propagate the changes
2. **Reinstall the app** in a development store (if needed)
3. **Open the app** in Shopify Admin
4. **Check**: Navigation should now appear in the LEFT SIDEBAR
5. **Verify**: All 5 menu items are visible

## What You've Already Done (Correct)

✅ Added `ui-nav-menu` element in `views/layouts/app.php`
✅ Added `<meta name="shopify-api-key">` tag
✅ App Bridge is properly initialized
✅ Navigation links are correctly structured

## What's Missing

❌ Partner Dashboard configuration to enable sidebar rendering
❌ This tells Shopify to render the menu in the sidebar (not top bar)

## How It Works Together

```
┌─────────────────────────────────────────────────────────┐
│ 1. Partner Dashboard Configuration                      │
│    → Tells Shopify: "This app has sidebar navigation"   │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│ 2. ui-nav-menu HTML (in your app.php)                   │
│    → Tells Shopify: "These are the navigation links"    │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│ 3. Shopify Admin Renders                                │
│    → Navigation appears in LEFT SIDEBAR                 │
└─────────────────────────────────────────────────────────┘
```

## Screenshots to Look For

In Partner Dashboard, you're looking for sections like:

- **App navigation**
- **Navigation menu**
- **Embedded app navigation**
- **App navigation links**

These sections allow you to add navigation items that will appear in the sidebar.

## Troubleshooting

### Can't Find Navigation Settings?

If you can't find the navigation settings in Partner Dashboard:

1. **Check app type**: Ensure your app is set as "Embedded app"
2. **Update app settings**: Go to App setup → Embedded app → Enable
3. **Contact Shopify Support**: Some older apps may need migration
4. **Use Shopify CLI**: Try `shopify app info` to see available features

### Navigation Still in Top Bar?

If navigation still appears in the top bar after configuration:

1. **Clear cache**: Clear browser cache and cookies
2. **Reinstall app**: Uninstall and reinstall in test store
3. **Wait**: Changes can take 5-10 minutes to propagate
4. **Check configuration**: Verify Partner Dashboard settings were saved

### Different Interface?

Shopify's Partner Dashboard interface varies by:
- Account type (Partner vs Staff)
- App creation date (older vs newer apps)
- Region/locale

Look for any section related to "navigation", "menu", or "app navigation".

## Expected Result

After configuration:

✅ Navigation appears in **LEFT SIDEBAR** (like Home, Orders, Products)
✅ Menu items: Dashboard, Reports, Chart Analysis, Schedule, Settings
✅ Active state highlights current page automatically
✅ Responsive: Sidebar on desktop, dropdown on mobile
✅ No more horizontal menu in the red box area

## Next Steps

1. ✅ Access Partner Dashboard
2. ✅ Find App Setup → Navigation section
3. ✅ Add the 5 navigation links
4. ✅ Save configuration
5. ✅ Wait 5-10 minutes
6. ✅ Test in Shopify Admin
7. ✅ Verify sidebar navigation appears

---

**Important**: The `ui-nav-menu` HTML is correct and ready. You just need to enable it in Partner Dashboard to make it render in the sidebar instead of the top bar.

**Status**: Awaiting Partner Dashboard configuration
**Expected Time**: 5-10 minutes after configuration
