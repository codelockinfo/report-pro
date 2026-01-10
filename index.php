<?php
/**
 * Report Pro - Shopify Embedded App
 * Main Entry Point
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define base paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Load .env file if it exists
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue; // Skip empty lines and comments
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove quotes if present
            $value = trim($value, '"\'');
            if (!empty($name)) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
            }
        }
    }
}

// Load autoloader
if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    http_response_code(500);
    die("Error: Composer dependencies not installed. Please run 'composer install' on your server.");
}
require_once ROOT_PATH . '/vendor/autoload.php';

// Load configuration
require_once CONFIG_PATH . '/config.php';
// Database connection is loaded lazily via Database class
// require_once CONFIG_PATH . '/database.php'; // Not needed - Database class handles connections

// Load core classes
require_once APP_PATH . '/Core/Router.php';
require_once APP_PATH . '/Core/Controller.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Core/View.php';
require_once APP_PATH . '/Core/Database.php';

// Initialize router
try {
    $router = new App\Core\Router();

    // Define routes
    require_once CONFIG_PATH . '/routes.php';

    // Dispatch request
    $router->dispatch();
} catch (\Exception $e) {
    // Log error
    error_log("Application error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Show user-friendly error
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Application Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
            .details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px; font-family: monospace; font-size: 12px; }
        </style>
    </head>
    <body>
        <h1>Application Error</h1>
        <div class="error">
            <strong>An error occurred while processing your request.</strong>
        </div>
        <div class="details">
            <strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?><br>
            <strong>File:</strong> <?= htmlspecialchars($e->getFile()) ?><br>
            <strong>Line:</strong> <?= $e->getLine() ?>
        </div>
        <p style="margin-top: 20px;">
            <small>Check server error logs for more details.</small>
        </p>
    </body>
    </html>
    <?php
    exit;
}

