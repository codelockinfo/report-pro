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
require_once ROOT_PATH . '/vendor/autoload.php';

// Load configuration
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

// Load core classes
require_once APP_PATH . '/Core/Router.php';
require_once APP_PATH . '/Core/Controller.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Core/View.php';
require_once APP_PATH . '/Core/Database.php';

// Initialize router
$router = new App\Core\Router();

// Define routes
require_once CONFIG_PATH . '/routes.php';

// Dispatch request
$router->dispatch();

