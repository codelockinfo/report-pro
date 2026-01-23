<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Shopify App Bridge (Latest) - MUST be first script -->
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    
    <title><?= $title ?? 'Report Pro' ?> - Shopify App</title>
    
    <!-- Shopify Configuration -->
    <meta name="shopify-api-key" content="<?= $config['shopify']['api_key'] ?>">
    <meta name="shopify-shop-domain" content="<?= $shop['shop_domain'] ?? '' ?>">
    
    <!-- Shopify Polaris CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@10.0.0/build/esm/styles.css" />
    
    <!-- Chart.js for reports -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f8;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .Polaris-Page {
            max-width: 100%;
            padding: 2rem;
        }
        
        /* Immediate fix for duplicate navigation menu - prevents flash */
        ui-nav-menu:not(:first-of-type) {
            display: none !important;
        }
    </style>
</head>
<body>
    <!-- Shopify Title Bar -->
    <ui-title-bar title="<?= $title ?? 'Report Pro' ?>">
        <button variant="primary" onclick="window.open('https://reportpro.codelocksolutions.com/docs', '_blank')">
            Help ❔
        </button>
    </ui-title-bar>
    
    <!-- Shopify Navigation Menu -->
    <ui-nav-menu>
        <a href="<?= $baseUrl ?>/" rel="home">Report Pro</a>
        <a href="<?= $baseUrl ?>/dashboard">Dashboard</a>
        <a href="<?= $baseUrl ?>/reports">Reports</a>
        <a href="<?= $baseUrl ?>/chart-analysis">Chart Analysis</a>
        <a href="<?= $baseUrl ?>/schedule">Schedule</a>
        <a href="<?= $baseUrl ?>/settings">Settings</a>
    </ui-nav-menu>
    
    <div id="app">
        <?= $content ?>
    </div>
    
    <script>
        // App Bridge auto-initializes with the meta tags
        // Just add some debugging and cleanup
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const host = urlParams.get('host');
            
            // Debug: Check if UI elements exist
            const titleBar = document.querySelector('ui-title-bar');
            const navMenu = document.querySelector('ui-nav-menu');
            
            console.log('=== App Bridge Debug Info ===');
            console.log('Host parameter:', host || 'MISSING');
            console.log('ui-title-bar element:', titleBar ? 'FOUND' : 'NOT FOUND');
            console.log('ui-nav-menu element:', navMenu ? 'FOUND' : 'NOT FOUND');
            
            if (titleBar) {
                console.log('ui-title-bar HTML:', titleBar.outerHTML.substring(0, 200));
            }
            if (navMenu) {
                console.log('ui-nav-menu HTML:', navMenu.outerHTML.substring(0, 200));
                console.log('ui-nav-menu children count:', navMenu.children.length);
            }
            
            if (!host) {
                console.warn("⚠️ Warning: 'host' parameter is missing in URL. App Bridge UI elements require this parameter.");
                console.log("💡 Tip: Add ?host=YOUR_HOST_PARAM to the URL");
            } else {
                console.log("✅ App Bridge should auto-initialize with host:", host);
            }
            
            // Clean up any duplicate navigation menus that might be injected
            setTimeout(() => {
                const navMenus = document.querySelectorAll('ui-nav-menu');
                if (navMenus.length > 1) {
                    console.log(`Found ${navMenus.length} nav menus, removing duplicates`);
                    for (let i = 1; i < navMenus.length; i++) {
                        navMenus[i].remove();
                    }
                }
            }, 1000);
        });
    </script>
</body>
</html>
