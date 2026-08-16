<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-App-API-Key');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$settings = getSettings();
$apiKey = $settings['appApiKey'] ?? '';

// Verify App API Key if set
$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$isWebUser = isset($_SESSION['user']) || isset($_SESSION['admin']);

if (!$isWebUser && !empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

function verifyToken($token) {
    global $jwtSecret;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $jwtSecret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    if (hash_equals($base64UrlSignature, $parts[2])) {
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        if (isset($payload['exp']) && $payload['exp'] >= time()) {
            return $payload;
        }
    }
    return null;
}

$action = $_GET['action'] ?? 'list';
$pdo = getPDO();

if ($action === 'list') {
    $movieSlug = $_GET['movie_slug'] ?? '';
    if (empty($movieSlug)) {
        echo json_encode(['status' => 'success', 'data' => [], 'average' => 0]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_name, rating_score, content, created_at FROM reviews WHERE movie_slug = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$movieSlug]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalScore = 0;
    foreach ($reviews as $r) {
        $totalScore += (int)$r['rating_score'];
    }
    $average = count($reviews) > 0 ? round($totalScore / count($reviews), 1) : 0;

    echo json_encode([
        'status' => 'success',
        'data' => $reviews,
        'average' => $average,
        'total' => count($reviews)
    ]);
    exit;
}

if ($action === 'add') {
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    $user = verifyToken($token);

    if (!$user) {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
        }
    }

    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để đánh giá phim']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $movieSlug = $input['movie_slug'] ?? '';
    $rating = (int)($input['rating'] ?? 0);
    $content = $input['content'] ?? '';

    if (empty($movieSlug) || $rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ. Vui lòng chọn số sao.']);
        exit;
    }

    // Check if user already reviewed
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_email = ? AND movie_slug = ?");
    $stmt->execute([$user['email'], $movieSlug]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE reviews SET rating_score = ?, content = ?, created_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$rating, $content, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_email, user_name, movie_slug, rating_score, content) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user['email'], $user['name'] ?? 'User', $movieSlug, $rating, $content]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Đã lưu đánh giá']);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
