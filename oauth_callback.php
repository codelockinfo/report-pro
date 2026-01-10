<?php
/**
 * Shopify OAuth Callback Handler
 * 
 * This script handles the OAuth callback from Shopify after user authorization.
 * 
 * Flow:
 *   1. Shopify redirects here with code, shop, state, and hmac parameters
 *   2. Validate state token (CSRF protection)
 *   3. Validate HMAC signature
 *   4. Exchange authorization code for access token
 *   5. Save shop and access token to database
 *   6. Create session
 *   7. Redirect to app dashboard
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define paths
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('APP_PATH', ROOT_PATH . '/app');

// Load configuration
require_once CONFIG_PATH . '/config.php';
$config = require CONFIG_PATH . '/config.php';

// Load core classes
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Models/Shop.php';
use App\Models\Shop;
use App\Core\Database;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Validate HMAC signature from Shopify
 * 
 * @param array $params Query parameters
 * @param string $secret API secret
 * @return bool
 */
function validateHmac($params, $secret) {
    if (!isset($params['hmac'])) {
        return false;
    }
    
    $hmac = $params['hmac'];
    unset($params['hmac']);
    
    // Sort parameters
    ksort($params);
    
    // Build query string
    $message = http_build_query($params);
    
    // Calculate HMAC
    $calculatedHmac = hash_hmac('sha256', $message, $secret);
    
    // Compare HMACs (timing-safe comparison)
    return hash_equals($hmac, $calculatedHmac);
}

/**
 * Exchange authorization code for access token
 * 
 * @param string $shop Shop domain
 * @param string $code Authorization code
 * @param array $config Configuration array
 * @return array|false Token data or false on failure
 */
function exchangeCodeForToken($shop, $code, $config) {
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        logOAuth('cURL error during token exchange', [
            'shop' => $shop,
            'error' => $curlError
        ]);
        return false;
    }
    
    if ($httpCode !== 200) {
        logOAuth('Token exchange failed', [
            'shop' => $shop,
            'http_code' => $httpCode,
            'response' => $response
        ]);
        return false;
    }
    
    $tokenData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logOAuth('JSON decode error', [
            'shop' => $shop,
            'json_error' => json_last_error_msg()
        ]);
        return false;
    }
    
    return $tokenData;
}

/**
 * Log OAuth events for debugging
 * 
 * @param string $message Log message
 * @param array $data Additional data
 */
function logOAuth($message, $data = []) {
    $logFile = ROOT_PATH . '/storage/oauth.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logData = json_encode($data);
    $logEntry = "[{$timestamp}] {$message} | Data: {$logData}\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Redirect with error message
 * 
 * @param string $message Error message
 */
function redirectWithError($message) {
    $errorUrl = $config['app_url'] . '/auth/error?message=' . urlencode($message);
    header("Location: {$errorUrl}");
    exit;
}

// Get parameters from query string
$code = $_GET['code'] ?? '';
$shop = $_GET['shop'] ?? '';
$state = $_GET['state'] ?? '';
$hmac = $_GET['hmac'] ?? '';

// Log callback received
logOAuth('OAuth callback received', [
    'shop' => $shop,
    'has_code' => !empty($code),
    'has_state' => !empty($state),
    'has_hmac' => !empty($hmac),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

// Validate required parameters
if (empty($code)) {
    logOAuth('Missing authorization code');
    redirectWithError('Missing authorization code');
}

if (empty($shop)) {
    logOAuth('Missing shop parameter');
    redirectWithError('Missing shop parameter');
}

if (empty($state)) {
    logOAuth('Missing state parameter');
    redirectWithError('Missing state parameter');
}

// Validate state token (CSRF protection)
if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
    logOAuth('Invalid state token', [
        'expected' => $_SESSION['oauth_state'] ?? 'not set',
        'received' => $state
    ]);
    redirectWithError('Invalid state parameter. Please try installing again.');
}

// Validate shop matches session
if (!isset($_SESSION['oauth_shop']) || $_SESSION['oauth_shop'] !== $shop) {
    logOAuth('Shop mismatch', [
        'expected' => $_SESSION['oauth_shop'] ?? 'not set',
        'received' => $shop
    ]);
    redirectWithError('Shop parameter mismatch. Please try installing again.');
}

// Validate HMAC signature
if (!validateHmac($_GET, $config['shopify']['api_secret'])) {
    logOAuth('Invalid HMAC signature', [
        'shop' => $shop,
        'received_hmac' => $hmac
    ]);
    redirectWithError('Invalid HMAC signature. Security check failed.');
}

// Exchange code for access token
$tokenData = exchangeCodeForToken($shop, $code, $config);

if (!$tokenData) {
    redirectWithError('Failed to exchange authorization code for access token. Please try again.');
}

// Extract token data
$accessToken = $tokenData['access_token'] ?? '';
$scope = $tokenData['scope'] ?? '';

if (empty($accessToken)) {
    logOAuth('Empty access token received', [
        'shop' => $shop,
        'token_data' => $tokenData
    ]);
    redirectWithError('Access token not received. Please try installing again.');
}

// Save shop and access token to database
try {
    $shopModel = new Shop();
    $shopId = $shopModel->createOrUpdate($shop, [
        'access_token' => $accessToken,
        'scope' => $scope,
        'store_name' => $shop
    ]);
    
    logOAuth('Shop saved successfully', [
        'shop' => $shop,
        'shop_id' => $shopId
    ]);
} catch (Exception $e) {
    logOAuth('Database error', [
        'shop' => $shop,
        'error' => $e->getMessage()
    ]);
    redirectWithError('Database error. Please contact support.');
}

// Create session
$_SESSION['shopify_session'] = [
    'shop' => $shop,
    'shop_id' => $shopId,
    'access_token' => $accessToken,
    'scope' => $scope,
    'installed_at' => time()
];

// Clear OAuth session data
unset($_SESSION['oauth_state']);
unset($_SESSION['oauth_shop']);
unset($_SESSION['oauth_timestamp']);

// Log successful installation
logOAuth('OAuth installation completed successfully', [
    'shop' => $shop,
    'shop_id' => $shopId
]);

// Redirect to app dashboard
$dashboardUrl = $config['app_url'] . "/dashboard?shop={$shop}";
header("Location: {$dashboardUrl}");
exit;

