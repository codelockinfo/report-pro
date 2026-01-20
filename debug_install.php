<?php
/**
 * Debug Installation Script
 * Place this at: https://reportpro.codelocksolutions.com/debug_install.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Report-Pro Installation Debug</h1>";
echo "<hr>";

// Check 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 7.4+<br>";
echo phpversion() >= '7.4' ? "✅ OK" : "❌ FAILED";
echo "<hr>";

// Check 2: .env file
echo "<h2>2. Environment File</h2>";
if (file_exists(__DIR__ . '/.env')) {
    echo "✅ .env file exists<br>";
    $envContent = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            if (in_array($key, ['SHOPIFY_API_KEY', 'SHOPIFY_API_SECRET', 'DB_PASSWORD'])) {
                echo "$key = " . (empty(trim($value)) ? "❌ EMPTY" : "✅ SET") . "<br>";
            } else {
                echo "$key = " . htmlspecialchars(trim($value)) . "<br>";
            }
        }
    }
} else {
    echo "❌ .env file NOT found<br>";
}
echo "<hr>";

// Check 3: Database Connection
echo "<h2>3. Database Connection</h2>";
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value, '"\'');
        }
    }
    
    $host = $env['DB_HOST'] ?? 'localhost';
    $dbname = $env['DB_NAME'] ?? '';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';
    
    echo "Host: $host<br>";
    echo "Database: $dbname<br>";
    echo "User: $user<br>";
    echo "Password: " . (empty($pass) ? "❌ EMPTY" : "✅ SET") . "<br><br>";
    
    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo "✅ Database connection successful<br>";
        
        // Check if shops table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'shops'");
        if ($stmt->rowCount() > 0) {
            echo "✅ 'shops' table exists<br>";
            
            // Count shops
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM shops");
            $result = $stmt->fetch();
            echo "Shops in database: " . $result['count'] . "<br>";
        } else {
            echo "❌ 'shops' table NOT found<br>";
        }
    } catch (PDOException $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Cannot test database - .env file missing<br>";
}
echo "<hr>";

// Check 4: Required Files
echo "<h2>4. Required Files</h2>";
$requiredFiles = [
    'index.php',
    'app/Controllers/AuthController.php',
    'app/Core/Router.php',
    'app/Core/Database.php',
    'app/Models/Shop.php',
    'config/config.php',
    'config/routes.php',
    '.htaccess'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo ($exists ? "✅" : "❌") . " $file<br>";
}
echo "<hr>";

// Check 5: Composer Autoloader
echo "<h2>5. Composer Autoloader</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer autoloader exists<br>";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader loaded successfully<br>";
} else {
    echo "❌ Composer autoloader NOT found<br>";
    echo "Run: composer install<br>";
}
echo "<hr>";

// Check 6: Test OAuth Callback URL
echo "<h2>6. OAuth Callback Test</h2>";
$callbackUrl = "https://reportpro.codelocksolutions.com/auth/callback";
echo "Callback URL: <a href='$callbackUrl'>$callbackUrl</a><br>";
echo "Expected: Should show 'OAuth Error: Missing authorization code' or similar<br>";
echo "<hr>";

// Check 7: Error Logs
echo "<h2>7. Error Log Location</h2>";
echo "error_log setting: " . ini_get('error_log') . "<br>";
echo "log_errors: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "<br>";
echo "display_errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
echo "<hr>";

// Check 8: GET Parameters
echo "<h2>8. Test GET Parameters</h2>";
echo "Current URL: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>";
if (!empty($_GET)) {
    echo "GET Parameters:<br>";
    foreach ($_GET as $key => $value) {
        echo "  $key = " . htmlspecialchars($value) . "<br>";
    }
} else {
    echo "No GET parameters<br>";
}
echo "<hr>";

echo "<h2>✅ Debug Complete</h2>";
echo "<p>If all checks pass, try installing the app again and check the error logs.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Visit: <a href='/auth/callback'>Test Callback Route</a> (should show error message, not 404)</li>";
echo "<li>Check server error logs for OAuth messages</li>";
echo "<li>Try installing the app from Shopify admin</li>";
echo "</ol>";
?>
