<?php
/**
 * Database Configuration
 * 
 * This file provides a function to get database connection.
 * Connection is lazy - only connects when needed.
 */

$config = require __DIR__ . '/config.php';
$dbConfig = $config['database'];

/**
 * Get database connection (lazy connection)
 * 
 * @return PDO
 * @throws PDOException
 */
function getDatabaseConnection() {
    global $dbConfig;
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['name'],
                $dbConfig['charset']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
            
            // Set timezone
            $pdo->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Database config: host={$dbConfig['host']}, db={$dbConfig['name']}, user={$dbConfig['user']}");
            throw $e;
        }
    }
    
    return $pdo;
}

// For backward compatibility with legacy OAuth files
// Only create connection if explicitly requested via global $pdo
$pdo = null;

