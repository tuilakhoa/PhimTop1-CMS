<?php
require 'includes/db.php';
$pdo = getPDO();
if (!$pdo) {
    echo "No DB\n";
    exit;
}
$stmt = $pdo->query("DESCRIBE settings");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $cols) . "\n";
