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
    $stmt = $pdo->query("SELECT * FROM actor_reports ORDER BY created_at DESC");
    $approved_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Nếu chưa có dữ liệu, thêm một số dữ liệu mặc định phổ biến
    if (empty($approved_reports)) {
        $approved_reports = [
            ['actor_name' => 'Thành Long (Jackie Chan)', 'evidence_text' => 'Công khai ủng hộ đường lưỡi bò trên mạng xã hội Weibo.', 'status' => 'approved'],
            ['actor_name' => 'Dương Dương (Yang Yang)', 'evidence_text' => 'Chia sẻ hình ảnh đường lưỡi bò trên Weibo cá nhân năm 2016.', 'status' => 'approved'],
            ['actor_name' => 'Triệu Lệ Dĩnh (Zhao Liying)', 'evidence_text' => 'Đăng tải bài viết ủng hộ "Trung Quốc, một điểm cũng không thể thiếu".', 'status' => 'approved'],
            ['actor_name' => 'Địch Lệ Nhiệt Ba (Dilraba)', 'evidence_text' => 'Share bài viết của People\'s Daily với bản đồ đường lưỡi bò.', 'status' => 'approved'],
            ['actor_name' => 'Dương Tử (Yang Zi)', 'evidence_text' => 'Ủng hộ bản đồ có đường lưỡi bò, đăng bài khẳng định chủ quyền phi lý.', 'status' => 'approved'],
            ['actor_name' => 'Tiêu Chiến (Xiao Zhan)', 'evidence_text' => 'Chia sẻ bài viết "Trung Quốc, một tấc đất cũng không thể thiếu".', 'status' => 'approved'],
            ['actor_name' => 'Vương Nhất Bác (Wang Yibo)', 'evidence_text' => 'Đăng lại thông điệp bảo vệ bản đồ đường lưỡi bò của truyền thông Trung Quốc.', 'status' => 'approved'],
            ['actor_name' => 'Lưu Diệc Phi (Liu Yifei)', 'evidence_text' => 'Công khai chia sẻ hình ảnh "Một điểm cũng không thể thiếu".', 'status' => 'approved'],
            ['actor_name' => 'Dương Mịch (Yang Mi)', 'evidence_text' => 'Ủng hộ yêu sách đường lưỡi bò trên mạng xã hội Trung Quốc.', 'status' => 'approved'],
            ['actor_name' => 'Angelababy', 'evidence_text' => 'Share bản đồ có đường lưỡi bò trên trang Weibo cá nhân.', 'status' => 'approved'],
            ['actor_name' => 'Lý Hiện (Li Xian)', 'evidence_text' => 'Chia sẻ hình ảnh ủng hộ đường lưỡi bò.', 'status' => 'approved'],
            ['actor_name' => 'Cúc Tịnh Y (Ju Jingyi)', 'evidence_text' => 'Đăng tải bài viết bảo vệ quan điểm đường lưỡi bò của Trung Quốc.', 'status' => 'approved'],
            ['actor_name' => 'Hứa Khải (Xu Kai)', 'evidence_text' => 'Share bài viết ủng hộ bản đồ đường lưỡi bò trên Weibo.', 'status' => 'approved'],
            ['actor_name' => 'Ngô Lỗi (Wu Lei)', 'evidence_text' => 'Chia sẻ thông điệp "Một điểm cũng không thể thiếu".', 'status' => 'approved'],
            ['actor_name' => 'Trương Nghệ Hưng (Lay EXO)', 'evidence_text' => 'Tích cực chia sẻ hình ảnh và ủng hộ bản đồ có đường lưỡi bò.', 'status' => 'approved'],
            ['actor_name' => 'Tống Thiến (Victoria f(x))', 'evidence_text' => 'Đăng bản đồ đường lưỡi bò lên Instagram và Weibo cá nhân.', 'status' => 'approved'],
            ['actor_name' => 'Vương Hạc Đệ (Dylan Wang)', 'evidence_text' => 'Chia sẻ bài viết của truyền thông Trung Quốc chứa bản đồ đường lưỡi bò.', 'status' => 'approved'],
            ['actor_name' => 'Triệu Lộ Tư (Zhao Lusi)', 'evidence_text' => 'Share bài viết "Trung Quốc một điểm không thể thiếu".', 'status' => 'approved'],
            ['actor_name' => 'Bạch Lộc (Bai Lu)', 'evidence_text' => 'Chia sẻ thông điệp ủng hộ đường lưỡi bò trên Weibo.', 'status' => 'approved']
        ];
    }
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
