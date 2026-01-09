<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Report Pro' ?> - Shopify App</title>
    
    <!-- Shopify Polaris CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@latest/build/esm/styles.css" />
    
    <!-- App Bridge -->
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge-utils.js"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .Polaris-Page {
            padding: 0;
        }
    </style>
</head>
<body>
    <div id="app">
        <?= $content ?>
    </div>
    
    <script>
        // Initialize App Bridge
        const AppBridge = window['app-bridge'];
        const actions = AppBridge.actions;
        const utils = window['app-bridge-utils'];
        
        const app = AppBridge.default({
            apiKey: '<?= $config['shopify']['api_key'] ?>',
            shopOrigin: '<?= $shop['shop_domain'] ?? '' ?>',
            forceRedirect: true
        });
        
        // Set up title bar
        const TitleBar = actions.TitleBar;
        TitleBar.create(app, {
            title: '<?= $title ?? 'Report Pro' ?>'
        });
        
        // Set up loading bar
        const Loading = actions.Loading;
        const loading = Loading.create(app);
        
        // Show loading on navigation
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' && e.target.href) {
                loading.dispatch(Loading.Action.START);
            }
        });
    </script>
    
    <script src="/public/js/app.js"></script>
</body>
</html>

