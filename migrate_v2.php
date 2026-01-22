<?php
require_once __DIR__ . '/vendor/autoload.php';

// Hand-roll environment loading since we're outside the app core
$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, '"\' ');
    }
}

try {
    $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Adding columns to 'reports' table...\n";

    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'last_viewed_at'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE reports ADD COLUMN last_viewed_at DATETIME DEFAULT NULL");
        echo "Column 'last_viewed_at' added successfully.\n";
    } else {
        echo "Column 'last_viewed_at' already exists.\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'view_count'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE reports ADD COLUMN view_count INT DEFAULT 0");
        echo "Column 'view_count' added successfully.\n";
    } else {
        echo "Column 'view_count' already exists.\n";
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
