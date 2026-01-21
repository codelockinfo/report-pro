<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Report Pro' ?> - Shopify App</title>
    
    <!-- Shopify Polaris CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@10.0.0/build/esm/styles.css" />
    
    <!-- App Bridge 4.x - Modern API with Navigation Menu support -->
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
            console.log("ReportPro: Initializing App Bridge 4.x with host:", host);
        }

        // Initialize App Bridge 4.x
        if (host && window.shopify) {
            try {
                // Create app instance
                var app = window.shopify.app({
                    apiKey: '<?= $config['shopify']['api_key'] ?>',
                    host: host
                });
                
                console.log('ReportPro: App Bridge 4.x initialized successfully');
                
                // Configure Navigation Menu
                <?php
                $appUrl = getenv('APP_URL') ?: 'http://localhost/report-pro';
                $baseUrl = rtrim($appUrl, '/');
                $queryParams = $_GET;
                unset($queryParams['url']);
                $queryString = http_build_query($queryParams);
                $suffix = $queryString ? '?' . $queryString : '';
                ?>
                
                // Set up navigation using ui-nav-menu
                var navConfig = {
                    items: [
                        {
                            label: 'Reports',
                            destination: '<?= $baseUrl ?>/reports<?= $suffix ?>'
                        },
                        {
                            label: 'Chart Analysis',
                            destination: '<?= $baseUrl ?>/chart-analysis<?= $suffix ?>'
                        },
                        {
                            label: 'Schedule',
                            destination: '<?= $baseUrl ?>/schedule<?= $suffix ?>'
                        },
                        {
                            label: 'Settings',
                            destination: '<?= $baseUrl ?>/settings<?= $suffix ?>'
                        }
                    ]
                };
                
                // Try to set navigation
                if (app.navigation) {
                    app.navigation.set(navConfig);
                    console.log('ReportPro: Navigation menu configured');
                } else {
                    console.warn('ReportPro: Navigation API not available');
                }
                
            } catch (e) {
                console.error('ReportPro: Failed to initialize App Bridge:', e);
            }
        }
    </script>
</body>
</html>

