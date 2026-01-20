<?php

namespace App\Core;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $config = require CONFIG_PATH . '/config.php';
        $dbConfig = $config['database'];

        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['name'],
                $dbConfig['charset']
            );

            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new \PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
            $this->pdo->exec("SET time_zone = '+00:00'");

        } catch (\PDOException $e) {
            $errorMsg = "Database connection failed: " . $e->getMessage();
            error_log($errorMsg);
            error_log("Database config: host={$dbConfig['host']}, db={$dbConfig['name']}, user={$dbConfig['user']}");
            
            // Provide helpful error message
            $helpfulMsg = "Database connection failed. ";
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                $helpfulMsg .= "Invalid database credentials. Please check DB_USER and DB_PASSWORD in your .env file.";
            } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                $helpfulMsg .= "Database '{$dbConfig['name']}' does not exist. Please create it or check DB_NAME in your .env file.";
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'getaddrinfo') !== false) {
                $helpfulMsg .= "Cannot connect to database server at {$dbConfig['host']}:{$dbConfig['port']}. Please check DB_HOST and DB_PORT in your .env file.";
            } else {
                $helpfulMsg .= "Please check your database configuration in the .env file.";
            }
            
            throw new \Exception($helpfulMsg);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    public function __clone()
    {
        throw new \Exception("Cannot clone singleton");
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
