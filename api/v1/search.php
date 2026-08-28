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

$keyword = $_GET['keyword'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

if (empty($keyword)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing keyword parameter']);
    exit;
}


    // Fetch data from CMS helper
    $data = fetchApiFilms('search', '', $page, $keyword);

    if ($data && !empty($data['items'])) {
        $repo = getMovieRepository();
        foreach ($data['items'] as $item) {
        }
    } else if (!$data) {
        // Fallback to local database cache if upstream API fails
        $data = [
            'items' => [],
            'titlePage' => 'Tìm kiếm: ' . htmlspecialchars($keyword),
            'domain' => '',
            'seoOnPage' => (object)[],
            'params' => (object)[],
            'pagination' => [
                'totalPages' => 1,
                'currentPage' => $page
            ]
        ];
        try {
            $repo = getMovieRepository();
            $result = $repo->getMovies($page, 24, $keyword);
            $data['items'] = $result['items'] ?? [];
            if (!empty($result['totalPages'])) {
                $data['pagination']['totalPages'] = $result['totalPages'];
            }
        } catch (Throwable $e) {
            // Ignore DB error and return empty result
        }
    }

if (!$data) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch search results']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
