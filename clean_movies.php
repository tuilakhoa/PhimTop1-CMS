<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/logger.php';

// Only allow admin access if needed, but since it's a utility script, we can just run it.
// To be safe, we might require admin login, or just let them delete it after running.
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    die('Vui lòng đăng nhập vào trang Admin trước khi chạy script này.');
}

$pdo = getPDO();
if (!$pdo) {
    die('Không thể kết nối Database.');
}

try {
    // Tìm số lượng phim sẽ bị xoá
    $stmt = $pdo->query("SELECT COUNT(*) FROM movies WHERE slug NOT IN (SELECT DISTINCT movie_slug FROM episodes)");
    $orphanedCount = $stmt->fetchColumn();

    if (isset($_GET['confirm']) && $_GET['confirm'] == '1') {
        $pdo->exec("DELETE FROM movies WHERE slug NOT IN (SELECT DISTINCT movie_slug FROM episodes)");
        echo "<h1>Hoàn tất!</h1>";
        echo "<p>Đã xoá thành công <strong>$orphanedCount</strong> phim rác (không có tập phim) khỏi hệ thống.</p>";
        echo "<p><a href='/'>Quay lại trang chủ</a></p>";
    } else {
        echo "<h1>Dọn dẹp Database</h1>";
        echo "<p>Hệ thống tìm thấy <strong>$orphanedCount</strong> phim bị lưu cache nhưng không có tập phim.</p>";
        if ($orphanedCount > 0) {
            echo "<p><a href='?confirm=1' style='background: red; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Xác nhận Xoá $orphanedCount phim này</a></p>";
        } else {
            echo "<p>Dữ liệu của bạn đã sạch sẽ! Không có phim rác nào.</p>";
            echo "<p><a href='/'>Quay lại trang chủ</a></p>";
        }
    }
} catch (Exception $e) {
    die('Lỗi: ' . $e->getMessage());
}
