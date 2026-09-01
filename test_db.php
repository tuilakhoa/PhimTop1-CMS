<?php
$config = json_decode(file_get_contents('config.json'), true);
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'] ?? '');
    echo "Connected!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
