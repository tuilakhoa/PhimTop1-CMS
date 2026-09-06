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
    
    // Thể Loại & Quốc Gia
    $sources = [
        'https://phimapi.com',
        'https://vsmov.com',
        'https://phim.nguonc.com/api' // Thử endpoint có thể có của NguonC
    ];
    
    $allGenres = [];
    $allCountries = [];
    $seenGenres = [];
    $seenCountries = [];
    
    foreach ($sources as $base) {
        $genresData = fetchApi($base . '/the-loai');
        $countriesData = fetchApi($base . '/quoc-gia');
        
        $genres = $genresData['data']['items'] ?? $genresData['items'] ?? [];
        $countries = $countriesData['data']['items'] ?? $countriesData['items'] ?? [];
        
        foreach ($genres as $item) {
            if (!empty($item['slug']) && !empty($item['name']) && !isset($seenGenres[$item['slug']])) {
                $allGenres[] = $item;
                $seenGenres[$item['slug']] = true;
            }
        }
        foreach ($countries as $item) {
            if (!empty($item['slug']) && !empty($item['name']) && !isset($seenCountries[$item['slug']])) {
                $allCountries[] = $item;
                $seenCountries[$item['slug']] = true;
            }
        }
    }
        
    $genresCount = 0;
    foreach ($allGenres as $item) {
        $repo->saveCategory($item['slug'], $item['name'], 'genre');
        $genresCount++;
    }
    
    $countriesCount = 0;
    foreach ($allCountries as $item) {
        $repo->saveCategory($item['slug'], $item['name'], 'country');
        $countriesCount++;
    }
    
    echo json_encode([
        'status' => 'success',
        'genres' => $genresCount,
        'countries' => $countriesCount
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
