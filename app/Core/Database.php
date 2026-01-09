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
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new \PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
            $this->pdo->exec("SET time_zone = '+00:00'");

        } catch (\PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \Exception("Database connection failed");
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

