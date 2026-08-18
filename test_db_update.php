<?php
require_once 'includes/db.php';

// First simulate checking columns
$pdo = getPDO();
$stmt = $pdo->query("SHOW COLUMNS FROM settings");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Settings columns: " . implode(", ", $cols) . "\n";

// Run migration
require_once 'includes/migrate.php';
runMigrations();

$stmt = $pdo->query("SHOW COLUMNS FROM settings");
$cols2 = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Settings columns after migration: " . implode(", ", $cols2) . "\n";
