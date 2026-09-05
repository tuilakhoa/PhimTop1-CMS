<?php
require 'includes/db.php';
$pdo = getPDO();
$stmt = $pdo->query("SHOW COLUMNS FROM movies LIKE '%_vote'");
print_r($stmt->fetchAll());
