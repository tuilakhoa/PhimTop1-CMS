<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/db.php';

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

$pdo = getPDO();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = (int)($_GET['offset'] ?? 0);

    // Get random shorts or order by created_at
    // We join with movies table to get the movie details
    $stmt = $pdo->prepare("
        SELECT s.*, m.name as movie_name, m.thumb_url as movie_thumb 
        FROM movie_shorts s
        LEFT JOIN movies m ON s.movie_slug = m.slug
        ORDER BY RAND() 
        LIMIT ? OFFSET ?
    ");
    
    // Bind values properly for LIMIT
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $shorts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no shorts found in db, return mock data for demonstration
    if (empty($shorts)) {
        $shorts = [
            [
                'id' => 1,
                'movie_slug' => 'one-piece',
                'short_video_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'title' => 'Cảnh chiến đấu đỉnh cao',
                'movie_name' => 'One Piece',
                'movie_thumb' => 'https://phimimg.com/upload/vod/20230219-1/afdc78a16db8a20d2d317ee0c36df12d.jpg'
            ],
            [
                'id' => 2,
                'movie_slug' => 'naruto',
                'short_video_url' => 'https://bitdash-a.akamaihd.net/content/MI201109210084_1/m3u8s/f08e80da-bf1d-4e3d-8899-f0f6155f6efa.m3u8',
                'title' => 'Naruto VS Sasuke',
                'movie_name' => 'Naruto',
                'movie_thumb' => 'https://phimimg.com/upload/vod/20230219-1/afdc78a16db8a20d2d317ee0c36df12d.jpg'
            ]
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $shorts
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
