<?php
/**
 * Error Log Viewer
 * Place at: https://reportpro.codelocksolutions.com/view_errors.php
 * This will show recent error log entries
 */

// Enable error display for this script only
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Error Log Viewer</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}";
echo "h1{color:#4ec9b0;}pre{background:#252526;padding:15px;border-radius:5px;overflow-x:auto;}";
echo ".error{color:#f48771;}.warning{color:#dcdcaa;}.info{color:#4fc1ff;}</style>";
echo "</head><body>";

echo "<h1>Error Log Viewer - Report-Pro</h1>";
echo "<p>Last updated: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Try to find error log
$possibleLogPaths = [
    __DIR__ . '/error.log',
    __DIR__ . '/storage/logs/error.log',
    __DIR__ . '/../logs/error.log',
    ini_get('error_log'),
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log'
];

$logFound = false;
$logPath = '';

foreach ($possibleLogPaths as $path) {
    if (!empty($path) && file_exists($path) && is_readable($path)) {
        $logPath = $path;
        $logFound = true;
        break;
    }
}

if ($logFound) {
    echo "<h2>✅ Error Log Found</h2>";
    echo "<p>Log file: <code>$logPath</code></p>";
    echo "<p>File size: " . number_format(filesize($logPath)) . " bytes</p>";
    echo "<hr>";
    
    // Read last 100 lines
    $lines = file($logPath);
    $totalLines = count($lines);
    $linesToShow = min(100, $totalLines);
    $startLine = max(0, $totalLines - $linesToShow);
    
    echo "<h2>Last $linesToShow Lines</h2>";
    echo "<pre>";
    
    for ($i = $startLine; $i < $totalLines; $i++) {
        $line = htmlspecialchars($lines[$i]);
        
        // Highlight errors
        if (stripos($line, 'fatal') !== false || stripos($line, 'error') !== false) {
            echo "<span class='error'>$line</span>";
        } elseif (stripos($line, 'warning') !== false) {
            echo "<span class='warning'>$line</span>";
        } elseif (stripos($line, 'oauth') !== false || stripos($line, 'shop') !== false) {
            echo "<span class='info'>$line</span>";
        } else {
            echo $line;
        }
    }
    
    echo "</pre>";
    
    // Filter for OAuth-related entries
    echo "<hr>";
    echo "<h2>OAuth-Related Entries (Last 50)</h2>";
    echo "<pre>";
    
    $oauthLines = array_filter($lines, function($line) {
        return stripos($line, 'oauth') !== false || 
               stripos($line, 'shop') !== false || 
               stripos($line, 'auth') !== false ||
               stripos($line, 'callback') !== false;
    });
    
    $oauthLines = array_slice($oauthLines, -50);
    
    if (empty($oauthLines)) {
        echo "No OAuth-related entries found.\n";
    } else {
        foreach ($oauthLines as $line) {
            echo "<span class='info'>" . htmlspecialchars($line) . "</span>";
        }
    }
    
    echo "</pre>";
    
} else {
    echo "<h2>❌ Error Log Not Found</h2>";
    echo "<p>Tried the following locations:</p>";
    echo "<ul>";
    foreach ($possibleLogPaths as $path) {
        $status = (!empty($path) && file_exists($path)) ? '✅ Exists' : '❌ Not found';
        echo "<li><code>$path</code> - $status</li>";
    }
    echo "</ul>";
    
    echo "<p><strong>Alternative:</strong> Check cPanel → Error Logs</p>";
}

echo "<hr>";
echo "<h2>PHP Error Reporting Settings</h2>";
echo "<pre>";
echo "error_reporting: " . error_reporting() . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "log_errors: " . ini_get('log_errors') . "\n";
echo "error_log: " . ini_get('error_log') . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><a href='debug_install.php'>← Back to Debug Install</a></p>";
echo "</body></html>";
?>
