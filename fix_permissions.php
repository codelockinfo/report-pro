<?php
// Fix Permissions Script V2
// This script generates a DIRECT Shopify OAuth URL to force scope updates.

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);
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
            }
        }
    }
}

// Load Config
$config = require CONFIG_PATH . '/config.php';

try {
    // Connect to Database using raw PDO
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset={$config['database']['charset']}",
        $config['database']['user'],
        $config['database']['password']
    );
    
    // Get the active shop
    $stmt = $pdo->query("SELECT shop_domain FROM shops WHERE id = 1 LIMIT 1");
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shop) {
        die("Error: No shop found in database. ");
    }

    $shopDomain = $shop['shop_domain'];
    $apiKey = $config['shopify']['api_key'];
    $scopes = $config['shopify']['scopes'];
    
    // Use the redirect URI from config. Ensure it matches Shopify Partner Dashboard!
    $redirectUri = urlencode($config['shopify']['redirect_uri']);
    
    // Generate Authorization URL
    // https://{shop}.myshopify.com/admin/oauth/authorize?client_id={api_key}&scope={scopes}&redirect_uri={redirect_uri}
    $installUrl = "https://$shopDomain/admin/oauth/authorize?client_id=$apiKey&scope=$scopes&redirect_uri=$redirectUri";

    // Output Page with Button
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Update Permissions</title>
        <meta charset="utf-8">
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "OS X", "Android", "Windows", sans-serif; text-align: center; padding: 50px; background-color: #f6f6f7; color: #202223; }
            .btn { background-color: #008060; color: white; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 16px; border: 1px solid transparent; cursor: pointer; display: inline-block; margin-top: 20px; transition: background-color 0.2s; }
            .btn:hover { background-color: #006e52; }
            .card { background: white; border-radius: 8px; box-shadow: 0 0 0 1px rgba(63, 63, 68, 0.05), 0 1px 3px 0 rgba(63, 63, 68, 0.15); padding: 30px; max-width: 500px; margin: 0 auto; }
            h1 { font-size: 24px; margin-bottom: 10px; }
            p { color: #6d7175; line-height: 1.5; }
            code { background: #f4f6f8; padding: 2px 4px; border-radius: 3px; font-family: monospace; font-size: 13px; color: #000; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Update Required</h1>
            <p>To enable full reporting history (365 days), the app requires the missing <code>read_all_orders</code> permission.</p>
            <p>Please click the button below to authorize the update.</p>
            <a href="' . $installUrl . '" class="btn">Grant Permissions</a>
            <br><br>
            <p><small style="font-size: 12px; color: #8c9196;">This will redirect you to Shopify to approve the scope.</small></p>
        </div>
    </body>
    </html>';
    exit;

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
