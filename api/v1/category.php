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

$type = $_GET['type'] ?? ''; // 'the-loai', 'quoc-gia', 'danh-sach'
$slug = $_GET['slug'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category = $_GET['category'] ?? '';
$country = $_GET['country'] ?? '';
$year = $_GET['year'] ?? '';

if (empty($type) || empty($slug)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing type or slug parameter']);
    exit;
}


    // Fetch data from CMS helper
    $data = fetchApiFilms($type, $slug, $page, '', $category, $country, $year);

    if ($data && !empty($data['items'])) {
        $repo = getMovieRepository();
        foreach ($data['items'] as $item) {
            $repo->saveMovie($item);
        }
    } else if (!$data) {
        $data = [
            'items' => [],
            'titlePage' => 'Danh mục: ' . htmlspecialchars($slug),
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
            $result = $repo->getMovies($page, 24, '');
            $data['items'] = $result['items'] ?? [];
            if (!empty($result['totalPages'])) {
                $data['pagination']['totalPages'] = $result['totalPages'];
            }
        } catch (Throwable $e) {}
    }

if (!$data) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch category data']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
