<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Report Pro' ?> - Shopify App</title>
    
    <!-- Shopify API Key for App Bridge Host -->
    <meta name="shopify-api-key" content="<?= $config['shopify']['api_key'] ?>">
    
    <!-- Shopify Polaris CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@10.0.0/build/esm/styles.css" />
    
    <!-- Shopify App Bridge (Latest) - Auto-initializes and renders ui-nav-menu in sidebar -->
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    
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
    </style>
</head>
<body>
    <!-- 
        Shopify Navigation Menu (ui-nav-menu)
        - The first <a> with rel="home" is REQUIRED and configures the home route
        - It is NOT rendered as a visible link in the sidebar
        - All subsequent <a> tags create the sidebar navigation menu
        - Shopify's App Bridge host automatically handles the active state based on current URL
        - Only flat lists are supported (no nested items)
    -->
    <ui-nav-menu>
        <!-- Required: Home route configuration (not rendered as a link) -->
        <a href="/" rel="home">Report Pro</a>
        
        <!-- Visible navigation items -->
        <a href="/dashboard">Dashboard</a>
        <a href="/reports">Reports</a>
        <a href="/chart-analysis">Chart Analysis</a>
        <a href="/schedule">Schedule</a>
        <a href="/settings">Settings</a>
    </ui-nav-menu>
    
    <div id="app">
        <?= $content ?>
    </div>
    
    
    <script>
        // App Bridge Latest - Auto-initializes from meta tag
        // The ui-nav-menu element will be automatically detected and rendered in the sidebar
        
        // Optional: Manual initialization for additional features
        document.addEventListener('DOMContentLoaded', function() {
            var urlParams = new URLSearchParams(window.location.search);
            var host = urlParams.get('host');
            var shop = urlParams.get('shop') || '<?= $shop['shop_domain'] ?? '' ?>';
            
            if (!host) {
                console.warn("ReportPro: Host parameter is missing. App Bridge may not initialize properly.");
            } else {
                console.log("ReportPro: App Bridge auto-initializing with host:", host);
                console.log("ReportPro: ui-nav-menu will render in sidebar automatically");
            }
            
            // Note: The latest App Bridge automatically:
            // 1. Detects the ui-nav-menu element
            // 2. Renders it in the LEFT SIDEBAR (desktop) or title bar dropdown (mobile)
            // 3. Manages active state based on current URL
            // 4. No manual initialization required!
        });
    </script>
</body>
</html>

