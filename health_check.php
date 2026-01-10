<?php
/**
 * Health Check - Quick Status Check
 * 
 * This file checks if the app is configured correctly.
 * Safe to keep on server for monitoring.
 */

header('Content-Type: application/json');

$status = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Check .env file exists
$envExists = file_exists(__DIR__ . '/.env');
$status['checks']['env_file'] = $envExists ? 'exists' : 'missing';

// Check database connection
$dbOk = false;
$dbError = null;
if ($envExists) {
    // Load .env
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
                $value = trim($value, '"\''); 
                if (!empty($name)) {
                    putenv($name . '=' . $value);
                }
            }
        }
    }
    
    try {
        $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
        $dbPort = getenv('DB_PORT') ?: '3306';
        $dbName = getenv('DB_NAME') ?: 'u402017191_report_pro';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASSWORD') ?: '';
        
        if (!empty($dbUser) && !empty($dbPass)) {
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2
            ]);
            $pdo->query("SELECT 1");
            $dbOk = true;
            $status['checks']['database'] = 'connected';
        } else {
            $status['checks']['database'] = 'credentials_missing';
        }
    } catch (Exception $e) {
        $dbError = $e->getMessage();
        $status['checks']['database'] = 'failed: ' . substr($dbError, 0, 50);
    }
} else {
    $status['checks']['database'] = 'skipped (no .env)';
}

// Check Shopify API key
$apiKey = getenv('SHOPIFY_API_KEY') ?: '';
$apiSecret = getenv('SHOPIFY_API_SECRET') ?: '';
$status['checks']['shopify_api_key'] = !empty($apiKey) ? 'configured' : 'missing';
$status['checks']['shopify_api_secret'] = !empty($apiSecret) ? 'configured' : 'missing';

// Overall status
if (!$envExists || !$dbOk || empty($apiKey) || empty($apiSecret)) {
    $status['status'] = 'error';
}

echo json_encode($status, JSON_PRETTY_PRINT);

