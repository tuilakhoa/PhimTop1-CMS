<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();
$data = [
    ['Dương Dương', 'Đăng tải hình ảnh "Đường lưỡi bò" và dòng trạng thái "Trung Quốc - một phân cũng không thể thiếu" trên Weibo.', 'https://tuoitre.vn/', 'approved'],
    ['Triệu Lệ Dĩnh', 'Chia sẻ bài viết vi phạm chủ quyền Biển Đông trên Weibo cá nhân.', 'https://tuoitre.vn/', 'approved'],
    ['Tiêu Chiến', 'Công khai ủng hộ bản đồ đường lưỡi bò phi pháp.', '', 'approved'],
    ['Lưu Diệc Phi', 'Ủng hộ đường lưỡi bò và các hành động vi phạm chủ quyền.', '', 'approved']
];
foreach($data as $row) {
    $stmt = $pdo->prepare("INSERT INTO actor_reports (actor_name, evidence_text, evidence_url, status) VALUES (?, ?, ?, ?)");
    $stmt->execute($row);
}
echo "Seeded data";
