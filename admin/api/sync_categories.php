<?php
require_once __DIR__ . '/../../includes/db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

function fetchApi($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

try {
    $repo = getCategoryRepository();
    
    // Fetch Thể Loại
    $genresData = fetchApi('https://phimapi.com/the-loai');
    $genres = $genresData['data']['items'] ?? [];
    
    // Fetch Quốc Gia
    $countriesData = fetchApi('https://phimapi.com/quoc-gia');
    $countries = $countriesData['data']['items'] ?? [];
        
    $genresCount = 0;
    foreach ($genres as $item) {
        if (!empty($item['slug']) && !empty($item['name'])) {
            $repo->saveCategory($item['slug'], $item['name'], 'genre');
            $genresCount++;
        }
    }
    
    $countriesCount = 0;
    foreach ($countries as $item) {
        if (!empty($item['slug']) && !empty($item['name'])) {
            $repo->saveCategory($item['slug'], $item['name'], 'country');
            $countriesCount++;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'genres' => $genresCount,
        'countries' => $countriesCount
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
