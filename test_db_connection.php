<?php
/**
 * Database Connection Test Script
 * 
 * Upload this to your server and visit it in browser to test database connection.
 * DELETE THIS FILE after testing for security!
 */

// Load environment variables
define('ROOT_PATH', __DIR__);
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"\''); // Remove quotes
            if (!empty($name)) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
            }
        }
    }
}

// Get database config
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'u402017191_report_pro';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .config { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>🔍 Database Connection Test</h1>
    
    <h2>Configuration (from .env file)</h2>
    <div class="config">
        <strong>Host:</strong> <?= htmlspecialchars($dbHost) ?><br>
        <strong>Port:</strong> <?= htmlspecialchars($dbPort) ?><br>
        <strong>Database:</strong> <?= htmlspecialchars($dbName) ?><br>
        <strong>User:</strong> <?= htmlspecialchars($dbUser) ?><br>
        <strong>Password:</strong> <?= $dbPass ? '***' . substr($dbPass, -2) : '<em>not set</em>' ?><br>
    </div>

    <?php if (empty($dbUser) || empty($dbPass)): ?>
        <div class="error">
            <strong>❌ Error:</strong> Database username or password is not set in .env file!
            <br><br>
            Please check your .env file and make sure DB_USER and DB_PASSWORD are set.
        </div>
    <?php else: ?>
        <h2>Connection Test</h2>
        <?php
        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            
            $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
            
            echo '<div class="success">';
            echo '<strong>✅ SUCCESS!</strong> Database connection successful!<br><br>';
            
            // Test query
            $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$dbName}'");
            $result = $stmt->fetch();
            $tableCount = $result['table_count'];
            
            echo "Database: <strong>{$dbName}</strong><br>";
            echo "Tables found: <strong>{$tableCount}</strong><br><br>";
            
            if ($tableCount > 0) {
                echo "✅ Database is ready to use!";
            } else {
                echo "⚠️ Database exists but has no tables. You may need to import the SQL file.";
            }
            
            echo '</div>';
            
            // Show tables if any
            if ($tableCount > 0) {
                echo '<h2>Database Tables</h2>';
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo '<div class="info">';
                echo '<strong>Tables in database:</strong><ul>';
                foreach ($tables as $table) {
                    echo '<li>' . htmlspecialchars($table) . '</li>';
                }
                echo '</ul></div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<strong>❌ CONNECTION FAILED!</strong><br><br>';
            echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '<br><br>';
            
            // Provide helpful suggestions
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                echo '<strong>💡 Solution:</strong> Check your DB_USER and DB_PASSWORD in .env file. The username or password is incorrect.';
            } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                echo '<strong>💡 Solution:</strong> Database name is wrong or database doesn\'t exist. Check DB_NAME in .env file.';
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'getaddrinfo') !== false) {
                echo '<strong>💡 Solution:</strong> Cannot connect to database server. Check DB_HOST and DB_PORT in .env file. Try "localhost" instead of "127.0.0.1" or vice versa.';
            } else {
                echo '<strong>💡 Solution:</strong> Check all database settings in your .env file.';
            }
            
            echo '</div>';
        }
        ?>
    <?php endif; ?>
    
    <div class="info" style="margin-top: 30px;">
        <strong>⚠️ Security Note:</strong> Delete this file (test_db_connection.php) after testing!
    </div>
</body>
</html>

