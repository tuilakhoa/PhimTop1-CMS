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
        
        $movie = $data['movie'];
        $movieImages = ['backdrops' => [], 'posters' => []];
        $tmdbId = $movie['tmdb']['id'] ?? null;
        $tmdbType = $movie['tmdb']['type'] ?? 'movie';
        $tmdbApiKey = $settings['tmdbApiKey'] ?? '';

        if ($tmdbId && $tmdbApiKey) {
            $tmdbRes = fetchApiWithCache("https://api.themoviedb.org/3/{$tmdbType}/{$tmdbId}/images?api_key=" . urlencode($tmdbApiKey), 86400);
            if ($tmdbRes) {
                $tmdbData = json_decode($tmdbRes, true);
                if (isset($tmdbData['backdrops'])) $movieImages['backdrops'] = $tmdbData['backdrops'];
                if (isset($tmdbData['posters'])) $movieImages['posters'] = $tmdbData['posters'];
            }
        } else {
            $imgRes = fetchApiWithCache("https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/images", 86400);
            if ($imgRes) {
                $imgData = json_decode($imgRes, true);
                if (isset($imgData['data'])) {
                    $movieImages['backdrops'] = $imgData['data']['backdrops'] ?? [];
                    $movieImages['posters'] = $imgData['data']['posters'] ?? [];
                }
            }
        }
        $data['images'] = $movieImages;
        
    } else if (!$data) {
        try {
            $repo = getMovieRepository();
            $movie = $repo->getMovieBySlug($slug);
            if ($movie) {
                $data = [
                    'movie' => $movie,
                    'episodes' => [], // Local cache might not have full episodes
                    'domain' => '',
                    'seoOnPage' => [],
                    'images' => ['backdrops' => [], 'posters' => []]
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
