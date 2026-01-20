<?php
/**
 * Simple OAuth Callback Test
 * Place at: https://reportpro.codelocksolutions.com/test_callback.php
 * 
 * This will test if the OAuth callback route is accessible
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>OAuth Callback Test</title></head><body>";
echo "<h1>OAuth Callback Test</h1>";
echo "<hr>";

// Test 1: Check if we can access this file
echo "<h2>✅ Test 1: PHP is working</h2>";
echo "If you see this, PHP is executing correctly.<br><br>";

// Test 2: Check GET parameters
echo "<h2>Test 2: GET Parameters</h2>";
if (!empty($_GET)) {
    echo "✅ GET parameters received:<br>";
    foreach ($_GET as $key => $value) {
        echo "  <strong>$key</strong> = " . htmlspecialchars($value) . "<br>";
    }
} else {
    echo "❌ No GET parameters<br>";
    echo "Try adding ?shop=test.myshopify.com to the URL<br>";
}
echo "<br>";

// Test 3: Try to load the app
echo "<h2>Test 3: Load Application</h2>";
try {
    define('ROOT_PATH', __DIR__);
    define('APP_PATH', ROOT_PATH . '/app');
    define('CONFIG_PATH', ROOT_PATH . '/config');
    
    echo "✅ Constants defined<br>";
    
    // Check if .env exists
    if (file_exists(ROOT_PATH . '/.env')) {
        echo "✅ .env file found<br>";
    } else {
        echo "❌ .env file NOT found<br>";
    }
    
    // Check if autoloader exists
    if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
        echo "✅ Composer autoloader found<br>";
        require_once ROOT_PATH . '/vendor/autoload.php';
        echo "✅ Autoloader loaded<br>";
    } else {
        echo "❌ Composer autoloader NOT found - run 'composer install'<br>";
    }
    
    // Check if config exists
    if (file_exists(CONFIG_PATH . '/config.php')) {
        echo "✅ config.php found<br>";
        $config = require CONFIG_PATH . '/config.php';
        echo "✅ Config loaded<br>";
        echo "App URL: " . ($config['app_url'] ?? 'NOT SET') . "<br>";
        echo "Shopify API Key: " . (empty($config['shopify']['api_key']) ? '❌ NOT SET' : '✅ SET') . "<br>";
    } else {
        echo "❌ config.php NOT found<br>";
    }
    
    // Try to instantiate AuthController
    if (class_exists('App\\Controllers\\AuthController')) {
        echo "✅ AuthController class exists<br>";
        
        // Check if callback method exists
        if (method_exists('App\\Controllers\\AuthController', 'callback')) {
            echo "✅ callback() method exists<br>";
        } else {
            echo "❌ callback() method NOT found<br>";
        }
    } else {
        echo "❌ AuthController class NOT found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If all tests pass, the app structure is correct</li>";
echo "<li>Try accessing: <a href='/auth/callback?code=test&shop=test.myshopify.com&state=test&hmac=test'>/auth/callback with test params</a></li>";
echo "<li>Check error logs for any OAuth messages</li>";
echo "</ol>";

echo "</body></html>";
?>
