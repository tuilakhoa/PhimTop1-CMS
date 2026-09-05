<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();
$stmt = $pdo->query("SELECT slug, trailer_url FROM movies WHERE trailer_url IS NOT NULL AND trailer_url != '' LIMIT 5");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
