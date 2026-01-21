<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Report Pro' ?> - Shopify App</title>
    
    <!-- Shopify Polaris CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@10.0.0/build/esm/styles.css" />
    
    <!-- App Bridge (Legacy) - Better for PHP apps without build tools -->
    <script src="https://unpkg.com/@shopify/app-bridge@3.7.10/umd/index.js"></script>
    <script src="https://unpkg.com/@shopify/app-bridge-utils@3.5.1/umd/index.js"></script>
    
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
        // Check if we are in an iframe (embedded)
        if (window.top === window.self) {
            // Not embedded - show warning or handle authentication
            // window.location.href = '/auth/install';
        }

        // Initialize App Bridge
        var AppBridge = window['app-bridge'];
        var actions = AppBridge.actions;
        var utils = window['app-bridge-utils'];
        
        // Get host from URL
        var urlParams = new URLSearchParams(window.location.search);
        var host = urlParams.get('host');
        var shop = urlParams.get('shop');
        
        // Config
        var config = {
            apiKey: '<?= $config['shopify']['api_key'] ?>',
            shopOrigin: shop || '<?= $shop['shop_domain'] ?? '' ?>',
            host: host,
            forceRedirect: true
        };
        
        // Debugging: If host is missing, let us know
        if (!host) {
            console.error("ReportPro: Host parameter is missing! App Bridge cannot initialize.");
            // Try to find host in parent URL if possible (usually blocked) or fallback
        } else {
            console.log("ReportPro: Initializing App Bridge with host:", host);
        }

        // Initialize
        if (host) {
            // Check if createApp is directly on AppBridge or default
            var createApp = AppBridge.create || AppBridge.default;
            var app = createApp(config);
            
            // Set up title bar
            var TitleBar = actions.TitleBar;
            var titleBar = TitleBar.create(app, {
                title: '<?= $title ?? 'Report Pro' ?>',
                breadcrumbs: <?= isset($breadcrumbs) ? json_encode($breadcrumbs) : 'null' ?>
            });
            
            // Set up Navigation Menu
            <?php
            $appUrl = getenv('APP_URL') ?: 'http://localhost/report-pro';
            $baseUrl = rtrim($appUrl, '/');
            $queryParams = $_GET;
            unset($queryParams['url']);
            $queryString = http_build_query($queryParams);
            $suffix = $queryString ? '?' . $queryString : '';
            ?>
            var NavigationMenu = actions.NavigationMenu;
            var navMenu = NavigationMenu.create(app, {
                items: [
                    {
                        label: 'Reports',
                        destination: '<?= $baseUrl ?>/reports<?= $suffix ?>',
                    },
                    {
                        label: 'Chart Analysis',
                        destination: '<?= $baseUrl ?>/chart-analysis<?= $suffix ?>',
                    },
                    {
                        label: 'Schedule',
                        destination: '<?= $baseUrl ?>/schedule<?= $suffix ?>',
                    },
                    {
                        label: 'Settings',
                        destination: '<?= $baseUrl ?>/settings<?= $suffix ?>',
                    }
                ]
            });
            
            // Set up History to sync with Shopify URL
            var History = actions.History;
            var shopifyHistory = History.create(app);
            
            // Update Shopify URL to match current route
            // Use explicit route from PHP if available (handles subdirectories correctly)
            // Otherwise fall back to window.location.pathname
            var path = '<?= $currentRoute ?? '' ?>';
            if (!path) {
                path = window.location.pathname;
            }
            // Use 'shopifyHistory' to avoid conflict with global window.history
            if (shopifyHistory && typeof shopifyHistory.dispatch === 'function') {
                shopifyHistory.dispatch(History.Action.REPLACE, path);
            } else {
                 console.warn("ReportPro: shopifyHistory.dispatch is not available");
            }

            // Set up loading bar
            var Loading = actions.Loading;
            var loading = Loading.create(app);
            
            // Show loading on navigation
            document.addEventListener('click', function(e) {
                var target = e.target.closest('a');
                if (target && target.href && !target.href.startsWith('#') && !target.href.startsWith('javascript:')) {
                    // Start loading
                    loading.dispatch(Loading.Action.START);
                    
                    // Allow normal navigation - App Bridge History will pick up the new page load
                }
            });
        }
    </script>
</body>
</html>

