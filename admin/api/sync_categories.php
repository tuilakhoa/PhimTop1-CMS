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
        'https://phim.nguonc.com/api'
    ];
    
    $allGenres = [];
    $allCountries = [];
    $seenGenres = [];
    $seenCountries = [];
    $seenGenresName = [];
    $seenCountriesName = [];
    
    foreach ($sources as $base) {
        $genresData = fetchApi($base . '/the-loai');
        $countriesData = fetchApi($base . '/quoc-gia');
        
        $genres = $genresData['data']['items'] ?? $genresData['items'] ?? [];
        $countries = $countriesData['data']['items'] ?? $countriesData['items'] ?? [];
        
        foreach ($genres as $item) {
            if (!empty($item['slug']) && !empty($item['name'])) {
                $nameLower = mb_strtolower(trim($item['name']));
                // Map a few common typos if necessary, e.g. lãng mạng -> lãng mạn
                if ($nameLower === 'lãng mạng') {
                    $item['name'] = 'Lãng Mạn';
                    $item['slug'] = 'lang-man';
                    $nameLower = 'lãng mạn';
                }
                
                if (!isset($seenGenres[$item['slug']]) && !isset($seenGenresName[$nameLower])) {
                    $allGenres[] = $item;
                    $seenGenres[$item['slug']] = true;
                    $seenGenresName[$nameLower] = true;
                }
            }
        }
        foreach ($countries as $item) {
            if (!empty($item['slug']) && !empty($item['name'])) {
                $nameLower = mb_strtolower(trim($item['name']));
                if (!isset($seenCountries[$item['slug']]) && !isset($seenCountriesName[$nameLower])) {
                    $allCountries[] = $item;
                    $seenCountries[$item['slug']] = true;
                    $seenCountriesName[$nameLower] = true;
                }
            }
        }
    }
    
    // Clear old data to remove duplicates
    $repo->clearCategories();
        
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