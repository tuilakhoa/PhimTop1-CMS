<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';

$settings = getSettings();
$apiKey = $settings['appApiKey'] ?? '';

// Verify API Key
$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');

if (!empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing movie slug']);
    exit;
}

$displayMode = $settings['displayMode'] ?? 'api';
if ($displayMode === 'crawl') {
    $repo = getMovieRepository();
    $movie = $repo->getMovieBySlug($slug);
    $data = null;
    if ($movie) {
        $data = [
            'movie' => $movie,
            'episodes' => [], // In DB only crawl mode, episodes are often not fully synced unless custom scraped
            'domain' => '',
            'seoOnPage' => []
        ];
    }
} else {
    // Fetch data from CMS helper
    $data = fetchApiMovieDetail($slug);

    if ($data && !empty($data['movie'])) {
        $repo = getMovieRepository();
        $repo->saveMovie($data['movie']);
    } else if (!$data) {
        try {
            $repo = getMovieRepository();
            $movie = $repo->getMovieBySlug($slug);
            if ($movie) {
                $data = [
                    'movie' => $movie,
                    'episodes' => [], // Local cache might not have full episodes
                    'domain' => '',
                    'seoOnPage' => []
                ];
            }
        } catch (Throwable $e) {}
    }
}

if (!$data || empty($data['movie'])) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Movie not found']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
