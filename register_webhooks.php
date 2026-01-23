<?php
/**
 * Register Webhooks for Existing Shop
 * 
 * This script registers webhooks for shops that are already installed
 */

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/config.php';

$config = require ROOT_PATH . '/config/config.php';

// Load .env
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value, '"\''));
        }
    }
}

echo "========================================\n";
echo "WEBHOOK REGISTRATION TOOL\n";
echo "========================================\n\n";

// Connect to database directly
try {
    $db = $config['database'];
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Connected to database\n\n";
} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Get all active shops
$stmt = $pdo->query("SELECT * FROM shops WHERE is_active = 1");
$shops = $stmt->fetchAll();

if (empty($shops)) {
    echo "No active shops found.\n";
    exit(0);
}

echo "Found " . count($shops) . " active shop(s)\n\n";

// Define webhooks to register
$webhookTopics = [
    'app/uninstalled' => '/webhooks/app/uninstalled',
    'customers/data_request' => '/webhooks/customers/data_request',
    'customers/redact' => '/webhooks/customers/redact',
    'shop/redact' => '/webhooks/shop/redact'
];

foreach ($shops as $shop) {
    echo "Processing shop: {$shop['shop_domain']}\n";
    echo str_repeat("-", 60) . "\n";
    
    $shopDomain = $shop['shop_domain'];
    $accessToken = $shop['access_token'];
    
    if (empty($accessToken)) {
        echo "  ✗ No access token found. Skipping.\n\n";
        continue;
    }
    
    // First, get existing webhooks
    $existingUrl = "https://{$shopDomain}/admin/api/{$config['shopify']['api_version']}/webhooks.json";
    
    $ch = curl_init($existingUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Shopify-Access-Token: ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "  ✗ Failed to fetch existing webhooks (HTTP {$httpCode})\n";
        echo "  Response: {$response}\n\n";
        continue;
    }
    
    $existingWebhooks = json_decode($response, true);
    $existingTopics = [];
    
    if (isset($existingWebhooks['webhooks'])) {
        foreach ($existingWebhooks['webhooks'] as $webhook) {
            $existingTopics[] = $webhook['topic'];
        }
    }
    
    echo "  Existing webhooks: " . (empty($existingTopics) ? "None" : implode(', ', $existingTopics)) . "\n";
    
    // Register each webhook
    foreach ($webhookTopics as $topic => $path) {
        if (in_array($topic, $existingTopics)) {
            echo "  ✓ {$topic} - Already registered\n";
            continue;
        }
        
        $webhookUrl = "https://{$shopDomain}/admin/api/{$config['shopify']['api_version']}/webhooks.json";
        
        $data = [
            'webhook' => [
                'topic' => $topic,
                'address' => $config['app_url'] . $path,
                'format' => 'json'
            ]
        ];
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201) {
            echo "  ✓ {$topic} - Registered successfully\n";
        } else {
            echo "  ✗ {$topic} - Failed (HTTP {$httpCode})\n";
            $responseData = json_decode($response, true);
            if (isset($responseData['errors'])) {
                echo "    Error: " . json_encode($responseData['errors']) . "\n";
            }
        }
    }
    
    echo "\n";
}

echo "========================================\n";
echo "WEBHOOK REGISTRATION COMPLETE\n";
echo "========================================\n";
echo "\nTo verify webhooks are registered:\n";
echo "1. Go to Shopify Admin: https://{$shopDomain}/admin/settings/notifications\n";
echo "2. Scroll down to 'Webhooks' section\n";
echo "3. You should see the registered webhooks\n";
