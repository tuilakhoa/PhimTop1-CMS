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

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    // Fetch data from CMS helper
    $data = fetchApiFilms('home', '', $page);

    if ($data && !empty($data['items'])) {
        $repo = getMovieRepository();
        foreach ($data['items'] as $item) {
        }
    } else if (!$data) {
        $data = [
            'items' => [],
            'titlePage' => 'Phim Mới Cập Nhật',
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

// Lấy danh sách phim nổi bật (Hero Banner)
$featuredMovies = [];
$featuredType = $settings['featuredType'] ?? 'latest';
$featuredStyle = $settings['featuredStyle'] ?? 'single';
$featuredCount = max(1, (int)($settings['featuredCount'] ?? 5));

if ($featuredType === 'admin') {
    $slugs = explode(',', $settings['featuredMovieSlug'] ?? '');
    foreach ($slugs as $s) {
        $s = trim($s);
        if (!$s) continue;
            $res = fetchApiMovieDetail($s);
            if ($res && !empty($res['movie'])) $featuredMovies[] = $res['movie'];
    }
} elseif ($featuredType === 'view') {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM movies ORDER BY view DESC LIMIT " . $featuredCount);
        $stmt->execute();
        $featuredMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $featuredMovies = array_slice($data['items'], 0, $featuredCount);
}

if ($featuredStyle === 'single' && count($featuredMovies) > 0) {
    $featuredMovies = [$featuredMovies[0]];
}
if (empty($featuredMovies) && !empty($data['items'])) {
    $featuredMovies = [$data['items'][0]];
}

$data['featuredMovies'] = $featuredMovies;
$data['featuredStyle'] = $featuredStyle;

if (!$data) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch data']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
