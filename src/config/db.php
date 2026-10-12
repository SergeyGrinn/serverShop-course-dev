<?php

require_once __DIR__ . '/../Core/bootstrap.php';

$env = static function (string $name, string $default = ''): string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
    return is_string($value) ? $value : $default;
};

define('DB_HOST', $env('DB_HOST', 'localhost'));
define('DB_NAME', $env('DB_NAME', 'server_eshop'));
define('DB_USER', $env('DB_USER', 'root'));
define('DB_PASS', $env('DB_PASS'));

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}