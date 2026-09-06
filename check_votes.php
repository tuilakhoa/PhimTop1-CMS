<?php
require 'includes/db.php';
$pdo = getPDO();
$stmt = $pdo->query("SELECT slug, tmdb_vote, imdb_vote FROM movies WHERE tmdb_vote > 0 OR imdb_vote > 0 LIMIT 5");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
