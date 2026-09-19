<?php
require_once 'includes/db.php';
$pdo = getPDO();
$stmt = $pdo->query("SELECT COUNT(*) FROM episodes WHERE embed_url LIKE '%upload18.%' OR embed_url LIKE '%xhub.network%'");
echo "18+ episodes: " . $stmt->fetchColumn() . "\n";
