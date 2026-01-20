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
        
        // Initialize
        if (host) {
            // Check if createApp is directly on AppBridge or default
            var createApp = AppBridge.create || AppBridge.default;
            var app = createApp(config);
            
            // Set up title bar
            var TitleBar = actions.TitleBar;
            var titleBar = TitleBar.create(app, {
                title: '<?= $title ?? 'Report Pro' ?>'
            });
            
            // Set up loading bar
            var Loading = actions.Loading;
            var loading = Loading.create(app);
            
            // Show loading on navigation
            document.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' && e.target.href && !e.target.href.startsWith('#')) {
                    loading.dispatch(Loading.Action.START);
                }
            });
        }
    </script>
</body>
</html>

