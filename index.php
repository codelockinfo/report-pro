<?php
/**
 * Report Pro - Shopify Embedded App
 * Main Entry Point
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define base paths

// Initialize router
try {
    $router = new App\Core\Router();

    // Define routes
    require_once CONFIG_PATH . '/routes.php';

    // Dispatch request
    $router->dispatch();
} catch (\Throwable $e) {
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

