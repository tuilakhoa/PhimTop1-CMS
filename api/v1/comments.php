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

$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');
if (!empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid API Key']);
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

$repo = getCommentRepository();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $slug = $_GET['slug'] ?? '';
    if (empty($slug)) {
        echo json_encode(['success' => false, 'message' => 'Movie slug is required.']);
        exit;
    }

    try {
        $comments = $repo->getCommentsByMovie($slug, true);

        // Format date
        foreach ($comments as &$c) {
            $createdAt = $c['created_at'] ?? 'now';
            if (empty($createdAt)) $createdAt = 'now';
            try {
                $date = new DateTime($createdAt);
            } catch (Exception $e) {
                $date = new DateTime();
            }
            $now = new DateTime();
            $diff = $now->diff($date);
            
            if ($diff->y > 0) $timeAgo = $diff->y . ' năm trước';
            elseif ($diff->m > 0) $timeAgo = $diff->m . ' tháng trước';
            elseif ($diff->d > 0) $timeAgo = $diff->d . ' ngày trước';
            elseif ($diff->h > 0) $timeAgo = $diff->h . ' giờ trước';
            elseif ($diff->i > 0) $timeAgo = $diff->i . ' phút trước';
            else $timeAgo = 'Vừa xong';
            
            $c['time_ago'] = $timeAgo;
        }

        echo json_encode(['success' => true, 'data' => $comments]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $slug = $data['slug'] ?? '';
    $name = trim($data['name'] ?? '');
    $content = trim($data['content'] ?? '');
    $anonymous = !empty($data['anonymous']) && $data['anonymous'] === true;

    if (empty($slug) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Mã phim và Nội dung không được để trống.']);
        exit;
    }

    // Try to get user name from token if not provided or to verify
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!empty($authHeader)) {
        $token = str_replace('Bearer ', '', $authHeader);
        $user = verifyToken($token);
        if ($user) {
            if (empty($name)) {
                $fs = getFirestore();
                $pdo = getPDO();
                if ($fs) {
                    $results = $fs->runQuery('members', 'email', 'EQUAL', $user['email'], 1);
                    if (!empty($results) && isset($results[0]['name'])) {
                        $name = $results[0]['name'];
                    }
                } else if ($pdo) {
                    $stmt = $pdo->prepare("SELECT name FROM members WHERE email = ?");
                    $stmt->execute([$user['email']]);
                    $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($dbUser) {
                        $name = $dbUser['name'];
                    }
                }
            }
        }
    }

    if ($anonymous || empty($name)) {
        $name = 'Ẩn danh';
    }

    if (mb_strlen($content) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Bình luận quá dài.']);
        exit;
    }

    try {
        $cleanName = htmlspecialchars($name);
        $cleanContent = htmlspecialchars($content);
        $newId = $repo->addComment($slug, $cleanName, $cleanContent, 'approved');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Bình luận thành công.',
            'comment' => [
                'id' => $newId,
                'user_name' => $cleanName,
                'content' => $cleanContent,
                'time_ago' => 'Vừa xong'
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ khi đăng bình luận.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
