<?php
function searchMeilisearch($keyword, $limit = 36, $offset = 0) {
    $host = 'http://127.0.0.1:7700';
    $masterKey = 'masterKey123';
    
    $url = rtrim($host, '/') . '/indexes/movies/search';
    $data = json_encode([
        'q' => $keyword,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $masterKey
    ]);
    // Timeout so it fails fast and we can fallback to MySQL
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $res = json_decode($response, true);
        if ($res && isset($res['hits'])) {
            return $res;
        }
    }
    return null;
}
