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
        // Clean up duplicate UI elements silently
        function cleanShopifyUI() {
            const navMenus = document.querySelectorAll('ui-nav-menu');
            if (navMenus.length > 1) {
                for (let i = 1; i < navMenus.length; i++) {
                    navMenus[i].remove();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initial cleanup
            cleanShopifyUI();
            
            // Periodically check for duplicates injected by App Bridge
            let count = 0;
            const interval = setInterval(() => {
                cleanShopifyUI();
                if (++count > 10) clearInterval(interval);
            }, 500);

            // Silent host/shop check for debugging
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.get('host')) {
                console.debug("Note: Host parameter is missing in URL.");
            }
        });
    </script>
</body>
</html>
