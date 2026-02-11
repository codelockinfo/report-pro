<?php
// Fix Permissions Script
// This script will redirect you to the app's installation URL to update permissions.
// Specifically, it requests the 'read_all_orders' scope needed for historical data.

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Load .env file manually
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
                $_ENV[$name] = $value;
            }
        }
    }
}

// Load Config
$config = require CONFIG_PATH . '/config.php';

try {
    // Connect to Database using raw PDO to avoid dependency issues with Framework classes
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset={$config['database']['charset']}",
        $config['database']['user'],
        $config['database']['password']
    );
    
    // Get the active shop
    $stmt = $pdo->query("SELECT shop_domain FROM shops WHERE id = 1 LIMIT 1");
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shop) {
        die("Error: No shop found in database. Please install the app first.");
    }

    $shopDomain = $shop['shop_domain'];
    $installUrl = $config['app_url'] . "/auth/install?shop=" . $shopDomain;

    // Output redirect script
    echo '<!DOCTYPE html>
    <html>
    <head><title>Fix Permissions</title></head>
    <body>
        <h1>Fixing Permissions...</h1>
        <p>Redirecting you to update app permissions (Requesting: read_all_orders)...</p>
        <script>window.location.href = "' . $installUrl . '";</script>
        <noscript><a href="' . $installUrl . '">Click here to continue</a></noscript>
    </body>
    </html>';
    exit;

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
