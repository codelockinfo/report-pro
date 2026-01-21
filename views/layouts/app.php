<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Report Pro' ?> - Shopify App</title>
    
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
                
                // Note: Navigation menu is handled by the horizontal nav bar at the top
                // Shopify sidebar navigation requires additional Partner Dashboard configuration
                
            } catch (e) {
                console.error('ReportPro: Failed to initialize App Bridge:', e);
            }
        }
    </script>
</body>
</html>

