<?php
// Check Scopes Live Script
// This script verifies the actual Shopify API Scopes granted to the app.

// Disable Framework Loading to prevent conflicts/missing classes
// require_once 'app/Core/Database.php'; 

// Load Config
$config = require 'config/config.php';

// Load .env manually to get DB credentials
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"\'');
            if (!empty($name)) {
                putenv($name . '=' . $value);
            }
        }
    }
}

try {
    // Connect DB using Raw PDO
    $dsn = "mysql:host=" . (getenv('DB_HOST') ?: $config['database']['host']) . 
           ";dbname=" . (getenv('DB_NAME') ?: $config['database']['name']) . 
           ";charset=utf8mb4";
           
    $pdo = new PDO($dsn, 
        getenv('DB_USER') ?: $config['database']['user'], 
        getenv('DB_PASSWORD') ?: $config['database']['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT shop_domain, access_token FROM shops WHERE id = 1 LIMIT 1");
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shop) {
        die("Error: No shop found in database.");
    }

    $domain = $shop['shop_domain'];
    $token = $shop['access_token'];

    echo "<h1>Checking Scopes for: $domain</h1>";
    
    // Call Shopify REST API to get access scopes
    $url = "https://$domain/admin/api/2024-01/access_scopes.json";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["X-Shopify-Access-Token: $token"]
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        echo "<h3 style='color:red'>API Error (HTTP $httpCode)</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        exit;
    }

    $data = json_decode($response, true);
    $scopes = $data['access_scopes'] ?? [];
    
    echo "<h3>Granted Scopes:</h3>";
    echo "<ul>";
    $hasReadAllOrders = false;
    foreach ($scopes as $scope) {
        $handle = $scope['handle'];
        echo "<li>" . htmlspecialchars($handle) . "</li>";
        if ($handle === 'read_all_orders') $hasReadAllOrders = true;
    }
    echo "</ul>";
    
    if ($hasReadAllOrders) {
        echo "<h2 style='color:green'>SUCCESS: 'read_all_orders' is PRESENT.</h2>";
    } else {
        echo "<h2 style='color:red'>FAILURE: 'read_all_orders' is MISSING.</h2>";
        // Also verify what config expects
        $configScopes = $config['shopify']['scopes'] ?? '';
        echo "<p>Config expects: " . htmlspecialchars($configScopes) . "</p>";
    }

} catch (Exception $e) {
    echo "<h3>Fatal Error:</h3>";
    echo $e->getMessage();
}
