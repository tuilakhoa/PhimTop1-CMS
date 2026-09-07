<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';

$settings = getSettings();
$apiKey = $settings['appApiKey'] ?? '';

// Verify API Key
$headers = getallheaders();
$clientApiKey = $_SERVER['HTTP_X_APP_API_KEY'] ?? ($headers['X-App-API-Key'] ?? ($headers['x-app-api-key'] ?? ($_GET['key'] ?? '')));

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

$repo = getMovieRepository();
if ($repo->isMovieBlocked($slug)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Movie is not available']);
    exit;
}


    // Fetch data from CMS helper
    $data = fetchApiMovieDetail($slug);
    if ($data && !empty($data['movie'])) {
    }

    if ($data && !empty($data['movie'])) {
        $movie = $data['movie'];
        
        // Sử dụng dữ liệu local (đã crawl) thay vì gọi API external
        $movieImages = ['backdrops' => [], 'posters' => []];
        if (!empty($movie['images_json'])) {
            $imagesData = json_decode($movie['images_json'], true);
            if (is_array($imagesData)) {
                $movieImages['backdrops'] = $imagesData['backdrops'] ?? [];
                $movieImages['posters'] = $imagesData['posters'] ?? [];
            }
        }
        $data['images'] = $movieImages;
        
        $peoples = [];
        if (!empty($movie['peoples_json'])) {
            $peoplesData = json_decode($movie['peoples_json'], true);
            if (is_array($peoplesData)) {
                $peoples = $peoplesData;
            }
        }
        
        // Fallback to basic actor/director fields if peoples_json is missing
        if (empty($peoples)) {
            if (!empty($movie['director'])) {
                $dirs = is_array($movie['director']) ? $movie['director'] : explode(',', $movie['director']);
                foreach ($dirs as $director) {
                    $director = trim($director);
                    if (!empty($director) && $director !== 'Đang cập nhật') {
                        $peoples[] = [
                            'name' => $director,
                            'character' => 'Đạo diễn',
                            'profile_path' => ''
                        ];
                    }
                }
            }
            if (!empty($movie['actor'])) {
                $acts = is_array($movie['actor']) ? $movie['actor'] : explode(',', $movie['actor']);
                foreach ($acts as $actor) {
                    $actor = trim($actor);
                    if (!empty($actor) && $actor !== 'Đang cập nhật') {
                        $peoples[] = [
                            'name' => $actor,
                            'character' => 'Diễn viên',
                            'profile_path' => ''
                        ];
                    }
                }
            }
        }

        $data['peoples'] = $peoples;
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

if (!$data || empty($data['movie'])) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Movie not found']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
