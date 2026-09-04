<?php
require 'includes/db.php';
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT slug, images_json FROM movies WHERE images_json IS NOT NULL AND images_json != '[]' LIMIT 1");
$stmt->execute();
$movie = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$movie) {
    echo "No movie with images_json found in local DB!\n";
} else {
    echo "Found movie: " . $movie['slug'] . "\n";
    echo "Length of images_json: " . strlen($movie['images_json']) . "\n";
    $data = json_decode($movie['images_json'], true);
    echo "Has images array? " . (isset($data['images']) ? 'Yes' : 'No') . "\n";
    if (isset($data['images'])) echo "Count images: " . count($data['images']) . "\n";
}
