<?php
/**
 * Complete OAuth Installation Example
 * 
 * This is a standalone, complete OAuth implementation that can be used
 * independently or integrated into your application.
 * 
 * SETUP INSTRUCTIONS:
 * 
 * 1. Configure your Shopify app credentials in the $config array below
 * 2. Set up your database connection
 * 3. Create the shops table (see database schema)
 * 4. Update redirect_uri in Shopify Partner Dashboard
 * 5. Visit: oauth_example.php?shop=your-shop.myshopify.com
 */

// ============================================================================
// CONFIGURATION
// ============================================================================

$config = [
    'shopify' => [
        'api_key' => 'YOUR_API_KEY_HERE',
        'api_secret' => 'YOUR_API_SECRET_HERE',
        'scopes' => 'read_orders,read_products,read_customers,read_inventory,read_transactions,read_analytics',
        'redirect_uri' => 'https://reportpro.codelocksolutions.com/oauth_callback.php',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'dbname' => 'u402017191_report_pro',
        'username' => 'root',
        'password' => '',
    ],
    'app_url' => 'https://reportpro.codelocksolutions.com',
];

// ============================================================================
// DATABASE SETUP
// ============================================================================

try {
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset=utf8mb4",
        $config['database']['username'],
        $config['database']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function validateShopDomain($shop) {
    return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*\.myshopify\.com$/', $shop);
}

function generateStateToken() {
    return bin2hex(random_bytes(32));
}

function validateHmac($params, $secret) {
    if (!isset($params['hmac'])) {
        return false;
    }
    
    $hmac = $params['hmac'];
    unset($params['hmac']);
    ksort($params);
    $message = http_build_query($params);
    $calculatedHmac = hash_hmac('sha256', $message, $secret);
    
    return hash_equals($hmac, $calculatedHmac);
}

function saveShop($pdo, $shop, $accessToken, $scope) {
    $stmt = $pdo->prepare("
        INSERT INTO shops (shop_domain, access_token, scope, store_name, created_at, updated_at)
        VALUES (?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            access_token = VALUES(access_token),
            scope = VALUES(scope),
            updated_at = NOW()
    ");
    
    return $stmt->execute([$shop, $accessToken, $scope, $shop]);
}

// ============================================================================
// OAUTH INSTALLATION HANDLER
// ============================================================================

if (!isset($_GET['action']) || $_GET['action'] === 'install') {
    // Start session
    session_start();
    
    // Get shop parameter
    $shop = $_GET['shop'] ?? '';
    
    if (empty($shop)) {
        die("Error: Shop parameter is required. Usage: ?shop=your-shop.myshopify.com");
    }
    
    // Validate shop domain
    if (!validateShopDomain($shop)) {
        die("Error: Invalid shop domain format. Must be: shopname.myshopify.com");
    }
    
    // Check API credentials
    if ($config['shopify']['api_key'] === 'YOUR_API_KEY_HERE') {
        die("Error: Please configure your Shopify API credentials in the script.");
    }
    
    // Generate state token
    $state = generateStateToken();
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_shop'] = $shop;
    
    // Build OAuth URL
    $authUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query([
        'client_id' => $config['shopify']['api_key'],
        'scope' => $config['shopify']['scopes'],
        'redirect_uri' => $config['shopify']['redirect_uri'],
        'state' => $state
    ]);
    
    // Redirect to Shopify
    header("Location: {$authUrl}");
    exit;
}

// ============================================================================
// OAUTH CALLBACK HANDLER
// ============================================================================

if (isset($_GET['action']) && $_GET['action'] === 'callback') {
    session_start();
    
    $code = $_GET['code'] ?? '';
    $shop = $_GET['shop'] ?? '';
    $state = $_GET['state'] ?? '';
    
    // Validate parameters
    if (empty($code) || empty($shop) || empty($state)) {
        die("Error: Missing required parameters from Shopify callback.");
    }
    
    // Validate state
    if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
        die("Error: Invalid state parameter. Possible CSRF attack.");
    }
    
    // Validate shop
    if (!isset($_SESSION['oauth_shop']) || $_SESSION['oauth_shop'] !== $shop) {
        die("Error: Shop parameter mismatch.");
    }
    
    // Validate HMAC
    if (!validateHmac($_GET, $config['shopify']['api_secret'])) {
        die("Error: Invalid HMAC signature. Security check failed.");
    }
    
    // Exchange code for token
    $tokenUrl = "https://{$shop}/admin/oauth/access_token";
    $data = [
        'client_id' => $config['shopify']['api_key'],
        'client_secret' => $config['shopify']['api_secret'],
        'code' => $code
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        die("Error: Failed to get access token. HTTP Code: {$httpCode}");
    }
    
    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'] ?? '';
    $scope = $tokenData['scope'] ?? '';
    
    if (empty($accessToken)) {
        die("Error: Access token not received from Shopify.");
    }
    
    // Save to database
    try {
        saveShop($pdo, $shop, $accessToken, $scope);
        
        // Create session
        $_SESSION['shopify_session'] = [
            'shop' => $shop,
            'access_token' => $accessToken,
            'scope' => $scope
        ];
        
        // Clear OAuth session
        unset($_SESSION['oauth_state']);
        unset($_SESSION['oauth_shop']);
        
        // Redirect to app
        $dashboardUrl = $config['app_url'] . "/dashboard?shop={$shop}";
        header("Location: {$dashboardUrl}");
        exit;
        
    } catch (PDOException $e) {
        die("Error: Database error - " . $e->getMessage());
    }
}

// ============================================================================
// DEFAULT: SHOW INSTALLATION FORM
// ============================================================================

?>
<!DOCTYPE html>
<html>
<head>
    <title>Shopify OAuth Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #5e8e3e;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #4a7c2f;
        }
        .info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Shopify OAuth Installation</h1>
    
    <div class="info">
        <strong>Instructions:</strong>
        <ol>
            <li>Enter your Shopify store domain (e.g., your-shop.myshopify.com)</li>
            <li>Click "Install App"</li>
            <li>Authorize the app in Shopify</li>
            <li>You'll be redirected back to the app</li>
        </ol>
    </div>
    
    <form method="GET" action="">
        <input type="hidden" name="action" value="install">
        
        <div class="form-group">
            <label for="shop">Shop Domain:</label>
            <input 
                type="text" 
                id="shop" 
                name="shop" 
                placeholder="your-shop.myshopify.com" 
                required
                pattern="[a-zA-Z0-9][a-zA-Z0-9-]*\.myshopify\.com"
            >
        </div>
        
        <button type="submit">Install App</button>
    </form>
    
    <script>
        // Auto-format shop domain
        document.getElementById('shop').addEventListener('blur', function(e) {
            let shop = e.target.value.trim();
            if (shop && !shop.includes('.')) {
                e.target.value = shop + '.myshopify.com';
            }
        });
    </script>
</body>
</html>

