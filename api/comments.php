<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getPDO();
    if ($pdo) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            movie_slug VARCHAR(255) NOT NULL,
            user_name VARCHAR(100) NOT NULL,
            content TEXT NOT NULL,
            status VARCHAR(50) DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
} catch (PDOException $e) {}

$method = $_SERVER['REQUEST_METHOD'];
$repo = getCommentRepository();

if ($method === 'GET') {
    // Fetch comments
    $slug = $_GET['slug'] ?? '';
    if (empty($slug)) {
        echo json_encode(['success' => false, 'message' => 'Movie slug is required.']);
        exit;
    }

    try {
        $comments = $repo->getCommentsByMovie($slug, true);

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
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    // Submit or delete comment
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $data['action'] ?? 'add';
    
    if ($action === 'delete') {
        session_start();
        $id = $data['id'] ?? 0;
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Comment ID is required.']);
            exit;
        }
        
        // Simple authorization check: allow if admin, or we just rely on JS hiding the button for others
        // In a real app we'd verify the comment owner's ID, but here we just check if it's logged in or admin
        // We'll allow it if the user is logged in and their name matches the comment's user_name, or if admin.
        
        // Let's get the comment first
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT user_name FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Bình luận không tồn tại.']);
            exit;
        }
        
        $isAdmin = isset($_SESSION['admin']);
        $currentUser = $_SESSION['user']['name'] ?? '';
        
        if (!$isAdmin && $currentUser !== $comment['user_name']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này.']);
            exit;
        }
        
        try {
            $repo->deleteComment($id);
            echo json_encode(['success' => true, 'message' => 'Đã xóa bình luận.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ khi xóa bình luận.']);
        }
        exit;
    }
    
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
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
