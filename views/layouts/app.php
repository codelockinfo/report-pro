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
        
        /* Immediate fix for duplicate navigation menu - prevents flash */
        /* Hides any duplicate ui-nav-menu elements instantly */
        ui-nav-menu:not(:first-of-type),
        ui-nav-menu:nth-of-type(n+2) {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            position: absolute !important;
            left: -9999px !important;
        }
    </style>
</head>
<body>
    <!-- Shopify Title Bar with Help Button -->
    <ui-title-bar title="<?= $title ?? 'Report Pro' ?>">
        <button variant="primary" onclick="window.open('https://reportpro.codelocksolutions.com/docs', '_blank')">
            Help 
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6z"/>
                <path d="M8 11c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1zM8 3C6.3 3 5 4.3 5 6h1.5c0-.8.7-1.5 1.5-1.5s1.5.7 1.5 1.5c0 1.5-2.5 1.3-2.5 3.5h1.5c0-1.6 2.5-1.8 2.5-3.5C11 4.3 9.7 3 8 3z"/>
            </svg>
        </button>
    </ui-title-bar>
    
    <!-- 
        Shopify Navigation Menu (ui-nav-menu)
        - The first <a> with rel="home" is REQUIRED and configures the home route
        - It is NOT rendered as a visible link in the sidebar
        - All subsequent <a> tags create the sidebar navigation menu
        - Shopify's App Bridge host automatically handles the active state based on current URL
        - Only flat lists are supported (no nested items)
    -->
    <ui-nav-menu>
        <a href="/" rel="home">Report Pro</a>
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
        
        // Function to remove duplicate navigation menus
        function removeDuplicateNavMenus() {
            var navMenus = document.querySelectorAll('ui-nav-menu');
            if (navMenus.length > 1) {
                console.warn('ReportPro: Removing', navMenus.length - 1, 'duplicate ui-nav-menu elements');
                for (var i = 1; i < navMenus.length; i++) {
                    navMenus[i].remove();
                }
                return true;
            }
            return false;
        }
        
        // Run immediately when DOM is ready
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
            
            // Remove duplicates immediately
            removeDuplicateNavMenus();
            
            // Monitor for duplicates being added dynamically
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeName === 'UI-NAV-MENU') {
                                removeDuplicateNavMenus();
                            }
                        });
                    }
                });
            });
            
            // Start observing the body for added ui-nav-menu elements
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Also check periodically for the first few seconds
            var checkCount = 0;
            var checkInterval = setInterval(function() {
                removeDuplicateNavMenus();
                checkCount++;
                if (checkCount >= 5) {
                    clearInterval(checkInterval);
                }
            }, 500);
        });
    </script>
</body>
</html>

