<?php
require_once __DIR__ . '/vendor/autoload.php';
$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, '"\' ');
    }
}
$dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4";
$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
$stmt = $pdo->query("SHOW COLUMNS FROM reports");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
