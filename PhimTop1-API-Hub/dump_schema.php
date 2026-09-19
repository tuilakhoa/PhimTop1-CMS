<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=phimtop1;charset=utf8', 'phimtop1', 'phimtop1');
$stmt = $pdo->query('SHOW CREATE TABLE episodes');
print_r($stmt->fetch());
