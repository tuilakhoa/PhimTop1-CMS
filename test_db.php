<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=phimtop1", "phimtop1", "phimtop1");
    $stmt = $pdo->query("SELECT name FROM movies WHERE name LIKE '%doraemon%' LIMIT 5");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($res);
} catch (Exception $e) {
    echo $e->getMessage();
}
