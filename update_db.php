<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'includes/db.php';
global $pdo;
if (!$pdo) {
    die("No PDO connection.\n");
}
try {
    $pdo->exec("ALTER TABLE settings ADD COLUMN smtp_host VARCHAR(255) DEFAULT ''");
    echo "Added smtp_host\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE settings ADD COLUMN smtp_port VARCHAR(10) DEFAULT ''");
    echo "Added smtp_port\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE settings ADD COLUMN smtp_user VARCHAR(255) DEFAULT ''");
    echo "Added smtp_user\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE settings ADD COLUMN smtp_pass VARCHAR(255) DEFAULT ''");
    echo "Added smtp_pass\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
    echo "Added reset_token\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN reset_expires DATETIME DEFAULT NULL");
    echo "Added reset_expires\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

echo "DB update complete.\n";
