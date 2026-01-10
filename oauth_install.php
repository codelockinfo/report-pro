<?php
/**
 * Shopify OAuth Installation Script
 * 
 * This script handles the OAuth 2.0 installation flow for Shopify apps.
 * 
 * Usage:
 *   Visit: https://reportpro.codelocksolutions.com/oauth_install.php?shop=your-shop.myshopify.com
 * 
 * Flow:
 *   1. User visits install URL with shop parameter
 *   2. Script validates shop domain
 *   3. Generates CSRF state token
 *   4. Redirects to Shopify OAuth authorization page
 *   5. User authorizes app
 *   6. Shopify redirects to callback URL
 *   7. Callback script exchanges code for access token
 *   8. Saves shop and token to database
 *   9. Redirects to app dashboard
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define paths
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');

// Load configuration
require_once CONFIG_PATH . '/config.php';
$config = require CONFIG_PATH . '/config.php';

// Database connection not needed for installation (only for callback)

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Validate shop domain format
 * 
 * @param string $shop Shop domain
 * @return bool
 */
function validateShopDomain($shop) {
    // Must be in format: shopname.myshopify.com
    return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*\.myshopify\.com$/', $shop);
}

/**
 * Generate secure random state token for CSRF protection
 * 
 * @return string
 */
function generateStateToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Build Shopify OAuth authorization URL
 * 
 * @param string $shop Shop domain
 * @param string $state CSRF state token
 * @param array $config Configuration array
 * @return string
 */
function buildAuthUrl($shop, $state, $config) {
    $params = [
        'client_id' => $config['shopify']['api_key'],
        'scope' => $config['shopify']['scopes'],
        'redirect_uri' => $config['shopify']['redirect_uri'],
        'state' => $state
    ];
    
    return "https://{$shop}/admin/oauth/authorize?" . http_build_query($params);
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

// Get shop parameter from URL
$shop = $_GET['shop'] ?? '';

// Validate shop parameter
if (empty($shop)) {
    http_response_code(400);
    die(json_encode([
        'error' => 'Missing shop parameter',
        'message' => 'Please provide a shop parameter: ?shop=your-shop.myshopify.com'
    ]));
}

// Remove any protocol or path
$shop = preg_replace('/^https?:\/\//', '', $shop);
$shop = preg_replace('/\/.*$/', '', $shop);

// Validate shop domain format
if (!validateShopDomain($shop)) {
    http_response_code(400);
    die(json_encode([
        'error' => 'Invalid shop domain',
        'message' => 'Shop domain must be in format: shopname.myshopify.com'
    ]));
}

// Check if API credentials are configured
if (empty($config['shopify']['api_key']) || $config['shopify']['api_key'] === 'your_api_key_here') {
    http_response_code(500);
    die(json_encode([
        'error' => 'API credentials not configured',
        'message' => 'Please configure SHOPIFY_API_KEY in config/config.php'
    ]));
}

// Generate state token for CSRF protection
$state = generateStateToken();

// Store state in session
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_shop'] = $shop;
$_SESSION['oauth_timestamp'] = time();

// Log OAuth initiation
logOAuth('OAuth installation initiated', [
    'shop' => $shop,
    'state' => $state,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

// Build authorization URL
$authUrl = buildAuthUrl($shop, $state, $config);

// Log redirect
logOAuth('Redirecting to Shopify OAuth', [
    'shop' => $shop,
    'auth_url' => $authUrl
]);

// Redirect to Shopify OAuth page
header("Location: {$authUrl}");
exit;

