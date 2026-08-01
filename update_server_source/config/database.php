<?php
$dbConfigPath = __DIR__ . '/db_config.json';

function getPDO() {
    global $dbConfigPath;
    if (!file_exists($dbConfigPath)) {
        return null;
    }
    
    $config = json_decode(file_get_contents($dbConfigPath), true);
    if (!$config) return null;
    
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password'] ?? '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}
