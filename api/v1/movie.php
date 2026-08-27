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

$repo = getMovieRepository();
if ($repo->isMovieBlocked($slug)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Movie is not available']);
    exit;
}


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
        
        $peoples = [];
        $peoplesRes = fetchApiWithCache("https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/peoples", 86400);
        if ($peoplesRes) {
            $pData = json_decode($peoplesRes, true);
            if (!empty($pData['data']['peoples'])) {
                $peoples = $pData['data']['peoples'];
            }
        }
        
        if (empty($peoples) && !empty($movie['imdb']['id'])) {
            $imdbId = $movie['imdb']['id'];
            $imdbRes = fetchApiWithCache("https://phimapi.com/imdb/title/" . urlencode($imdbId), 86400);
            if ($imdbRes) {
                $imdbData = json_decode($imdbRes, true);
                if (!empty($imdbData['movie']['director'])) {
                    foreach ((array)$imdbData['movie']['director'] as $director) {
                        if (!empty($director) && $director !== 'Đang cập nhật') {
                            $peoples[] = [
                                'name' => $director,
                                'character' => 'Đạo diễn',
                                'profile_path' => ''
                            ];
                        }
                    }
                }
                if (!empty($imdbData['movie']['actor'])) {
                    foreach ((array)$imdbData['movie']['actor'] as $actor) {
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
        }

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
