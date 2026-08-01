<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$pdo = getPDO();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        movie_slug VARCHAR(255) NOT NULL,
        user_name VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'approved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch comments
    $slug = $_GET['slug'] ?? '';
    if (empty($slug)) {
        echo json_encode(['success' => false, 'message' => 'Movie slug is required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, user_name, content, created_at FROM comments WHERE movie_slug = ? AND status = 'approved' ORDER BY created_at DESC");
        $stmt->execute([$slug]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format date
        foreach ($comments as &$c) {
            $date = new DateTime($c['created_at']);
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
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    // Submit comment
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $slug = $data['slug'] ?? '';
    $name = trim($data['name'] ?? '');
    $content = trim($data['content'] ?? '');
    $anonymous = !empty($data['anonymous']) && $data['anonymous'] === true;

    if (empty($slug) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Mã phim và Nội dung không được để trống.']);
        exit;
    }

    if ($anonymous || empty($name)) {
        $name = 'Ẩn danh';
    }

    // Basic spam prevention: check length
    if (mb_strlen($content) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Bình luận quá dài.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO comments (movie_slug, user_name, content, status) VALUES (?, ?, ?, 'approved')");
        $stmt->execute([$slug, htmlspecialchars($name), htmlspecialchars($content)]);
        
        $newId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Bình luận thành công.',
            'comment' => [
                'id' => $newId,
                'user_name' => htmlspecialchars($name),
                'content' => htmlspecialchars($content),
                'time_ago' => 'Vừa xong'
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ khi đăng bình luận.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
