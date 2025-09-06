<?php
// db.php — single PDO connection + BASE_URL helper
$configPath = __DIR__ . '/env.php';
if (!file_exists($configPath)) {
    // Fall back to sample if developer forgot to copy/rename
    $configPath = __DIR__ . '/env.sample.php';
}
$config = require $configPath;

$dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("❌ DB connection failed: " . $e->getMessage());
}

function base_url() {
    static $base = null;
    if ($base !== null) return $base;

    $config = require (file_exists(__DIR__.'/env.php') ? __DIR__.'/env.php' : __DIR__.'/env.sample.php');
    $base = rtrim($config['BASE_URL'] ?? '', '/');
    if (!$base && isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/.');
        $base = $scheme . '://' . $_SERVER['HTTP_HOST'] . $dir;
    }
    return $base;
}

session_start();
