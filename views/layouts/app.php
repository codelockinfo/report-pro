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
    
    <!-- App Bridge 3.x - Stable version for PHP apps -->
    <script src="https://unpkg.com/@shopify/app-bridge@3.7.10/umd/index.js"></script>
    
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
        // Get parameters from URL
        var urlParams = new URLSearchParams(window.location.search);
        var host = urlParams.get('host');
        var shop = urlParams.get('shop') || '<?= $shop['shop_domain'] ?? '' ?>';
        
        // Debugging
        if (!host) {
            console.error("ReportPro: Host parameter is missing! App Bridge cannot initialize.");
        } else {
            console.log("ReportPro: Initializing App Bridge 3.x with host:", host);
        }

        // Initialize App Bridge 3.x
        if (host && window['app-bridge']) {
            try {
                var AppBridge = window['app-bridge'];
                var createApp = AppBridge.createApp || AppBridge.default;
                
                var app = createApp({
                    apiKey: '<?= $config['shopify']['api_key'] ?>',
                    host: host,
                    forceRedirect: true
                });
                
                console.log('ReportPro: App Bridge initialized successfully');
                console.log('ReportPro: Sidebar navigation is handled by ui-nav-menu element');
                
                // Note: The ui-nav-menu element above automatically creates the sidebar navigation
                // Shopify's App Bridge host interprets the ui-nav-menu and renders it in the Admin
                // The active state is automatically managed based on the current URL
                
            } catch (e) {
                console.error('ReportPro: Failed to initialize App Bridge:', e);
            }
        }
    </script>
</body>
</html>

