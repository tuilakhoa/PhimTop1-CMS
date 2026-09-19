<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$pdo = getPDO();
if ($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS actor_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        actor_name VARCHAR(255) NOT NULL,
        evidence_text TEXT NOT NULL,
        evidence_url VARCHAR(500),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        reported_by VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['actor_name']) && !empty($_POST['evidence_text'])) {
        $actor_name = trim($_POST['actor_name']);
        $evidence_text = trim($_POST['evidence_text']);
        $evidence_url = trim($_POST['evidence_url'] ?? '');
        $reported_by = $_SESSION['user']['email'] ?? 'Guest';
        
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO actor_reports (actor_name, evidence_text, evidence_url, reported_by) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$actor_name, $evidence_text, $evidence_url, $reported_by])) {
                $message = '<div class="bg-green-900/50 border border-green-500 text-green-400 p-4 rounded-lg mb-6">Cảm ơn bạn! Báo cáo đã được gửi và đang chờ duyệt.</div>';
            } else {
                $message = '<div class="bg-red-900/50 border border-red-500 text-red-400 p-4 rounded-lg mb-6">Có lỗi xảy ra, vui lòng thử lại!</div>';
            }
        }
    }
}

$approved_reports = [];
if ($pdo) {
    // For demo purposes, we will also fetch pending if admin, but let's just fetch all or approved
    // Let's actually show ALL for now so the user can see it works, or we can auto-approve
    $stmt = $pdo->query("SELECT * FROM actor_reports ORDER BY created_at DESC");
    $approved_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$settings = getSettings();
$theme = $settings['theme'] ?? 'phimhayok';
$pageTitle = "Danh Sách Thần Tượng Vi Phạm Chủ Quyền - " . ($settings['siteName'] ?? "PhimTop1");

$themeFile = __DIR__ . "/themes/{$theme}/blacklist.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/blacklist.php";
}
