<?php
require_once __DIR__ . '/includes/db.php';
$settings = getSettings();
$theme = $settings['theme'] ?? 'phimhayok';
$page = $_GET['page'] ?? 'about';

$pages = [
    'about' => 'Giới thiệu',
    'contact' => 'Liên hệ',
    'terms' => 'Điều khoản dịch vụ',
    'privacy' => 'Chính sách bảo mật',
    'dmca' => 'Khiếu nại bản quyền'
];

if (!array_key_exists($page, $pages)) {
    $page = 'about';
}

$title = $pages[$page];
$isAppView = isset($_GET['app_view']) && $_GET['app_view'] == 1;

if (!$isAppView) {
    $headerPath = __DIR__ . "/themes/{$theme}/header.php";
    if (file_exists($headerPath)) include $headerPath;
} else {
    // Basic HTML for App WebView
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>'.$title.'</title>';
    echo '<style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #fff; background-color: #121212; padding: 20px; } a { color: #3498db; } h1, h2, h3 { color: #fff; } @media (prefers-color-scheme: light) { body { background-color: #fff; color: #333; } h1, h2, h3 { color: #111; } }</style>';
    echo '</head><body>';
}
?>

<div class="support-container" style="max-width: 1000px; margin: 40px auto; padding: 20px; color: <?php echo $isAppView ? 'inherit' : '#fff'; ?>;">
    <?php if (!$isAppView): ?>
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Sidebar -->
        <div style="flex: 1; min-width: 250px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; height: fit-content;">
            <h3 style="margin-top:0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">Trung tâm Hỗ trợ</h3>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($pages as $k => $v): ?>
                <li style="margin-bottom: 10px;">
                    <a href="?page=<?= $k ?>" style="color: <?= $page === $k ? '#3498db' : '#aaa' ?>; text-decoration: none; font-weight: <?= $page === $k ? 'bold' : 'normal' ?>; display: block; padding: 8px; border-radius: 6px; background: <?= $page === $k ? 'rgba(52, 152, 219, 0.1)' : 'transparent' ?>;"><?= $v ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- Content -->
        <div style="flex: 3; min-width: 300px; background: rgba(255,255,255,0.02); padding: 30px; border-radius: 12px;">
    <?php else: ?>
        <div>
    <?php endif; ?>

            <h1 style="margin-top:0; color: #3498db;"><?= $title ?></h1>
            <div style="line-height: 1.8;">
                <?php if ($page === 'about'): ?>
                    <p>Chào mừng bạn đến với <strong><?= htmlspecialchars($settings['site_name'] ?? 'PhimTop1') ?></strong> - Nền tảng xem phim trực tuyến hàng đầu.</p>
                    <p>Chúng tôi tự hào mang đến cho bạn trải nghiệm giải trí tuyệt vời nhất với hàng ngàn bộ phim chất lượng cao, đa dạng thể loại từ hành động, tình cảm, hài hước đến khoa học viễn tưởng.</p>
                    <h3>Tầm nhìn & Sứ mệnh</h3>
                    <p>Sứ mệnh của chúng tôi là kết nối cảm xúc qua từng thước phim, xây dựng một cộng đồng yêu phim văn minh và thân thiện. Chúng tôi không ngừng cập nhật công nghệ mới nhất để đảm bảo chất lượng hình ảnh sắc nét và tốc độ truyền tải mượt mà nhất.</p>
                
                <?php elseif ($page === 'contact'): ?>
                    <p>Chúng tôi luôn lắng nghe và sẵn sàng hỗ trợ bạn. Nếu có bất kỳ câu hỏi, góp ý hay yêu cầu nào, xin vui lòng liên hệ với chúng tôi qua các kênh sau:</p>
                    <ul>
                        <li><strong>Email:</strong> support@<?= $_SERVER['HTTP_HOST'] ?></li>
                        <li><strong>Hotline:</strong> 1900 xxxx (8:00 - 22:00 hàng ngày)</li>
                        <li><strong>Địa chỉ:</strong> Tòa nhà PhimTop1, Quận 1, TP.HCM</li>
                    </ul>
                    <p>Đội ngũ chăm sóc khách hàng của chúng tôi sẽ phản hồi bạn trong thời gian sớm nhất, thường là trong vòng 24 giờ làm việc.</p>
                
                <?php elseif ($page === 'terms'): ?>
                    <p>Việc sử dụng dịch vụ của <strong><?= htmlspecialchars($settings['site_name'] ?? 'PhimTop1') ?></strong> đồng nghĩa với việc bạn chấp nhận các điều khoản sau:</p>
                    <h3>1. Trách nhiệm người dùng</h3>
                    <p>Bạn đồng ý sử dụng nền tảng của chúng tôi cho mục đích giải trí cá nhân và phi thương mại. Mọi hành vi sao chép, phát চরম nội dung khi chưa được sự cho phép đều bị nghiêm cấm.</p>
                    <h3>2. Tài khoản</h3>
                    <p>Bạn tự chịu trách nhiệm bảo mật thông tin tài khoản và mật khẩu của mình. Chúng tôi có quyền khóa tài khoản nếu phát hiện dấu hiệu vi phạm điều khoản dịch vụ hoặc các hoạt động gian lận.</p>
                    <h3>3. Thay đổi điều khoản</h3>
                    <p>Chúng tôi bảo lưu quyền thay đổi các điều khoản này vào bất kỳ lúc nào. Những thay đổi sẽ được cập nhật công khai trên trang web và ứng dụng.</p>

                <?php elseif ($page === 'privacy'): ?>
                    <p>Chúng tôi cam kết bảo vệ thông tin cá nhân của bạn. Dưới đây là cách chúng tôi thu thập, sử dụng và bảo vệ dữ liệu:</p>
                    <h3>1. Thông tin thu thập</h3>
                    <p>Khi đăng ký, chúng tôi có thể thu thập email, tên hiển thị. Trong quá trình sử dụng, chúng tôi lưu lại lịch sử xem phim và danh sách yêu thích để cá nhân hóa trải nghiệm.</p>
                    <h3>2. Sử dụng thông tin</h3>
                    <p>Dữ liệu của bạn được dùng để duy trì tài khoản, đề xuất phim phù hợp và cải thiện chất lượng ứng dụng. Chúng tôi không bán dữ liệu của bạn cho bên thứ ba.</p>
                    <h3>3. Bảo mật</h3>
                    <p>Chúng tôi áp dụng các biện pháp bảo mật tiêu chuẩn để mã hóa và bảo vệ thông tin của bạn khỏi các truy cập trái phép.</p>

                <?php elseif ($page === 'dmca'): ?>
                    <p>Chúng tôi tôn trọng quyền sở hữu trí tuệ của người khác và tuân thủ Đạo luật Bản quyền Thiên niên kỷ Kỹ thuật số (DMCA).</p>
                    <p>Hầu hết các nội dung trên trang web này được lấy từ các nguồn chia sẻ công khai trên internet. Nếu bạn là chủ sở hữu bản quyền của bất kỳ tài liệu nào xuất hiện trên trang web của chúng tôi và muốn gỡ bỏ nó, vui lòng cung cấp các thông tin sau:</p>
                    <ul>
                        <li>Chữ ký (vật lý hoặc điện tử) của người được ủy quyền hành động thay mặt cho chủ sở hữu bản quyền.</li>
                        <li>Thông tin nhận dạng tác phẩm có bản quyền bị vi phạm.</li>
                        <li>Thông tin nhận dạng tài liệu vi phạm cần gỡ bỏ, bao gồm URL trỏ tới tài liệu đó.</li>
                        <li>Thông tin liên hệ của bạn (Email, số điện thoại).</li>
                    </ul>
                    <p>Gửi yêu cầu khiếu nại tới: <strong>dmca@<?= $_SERVER['HTTP_HOST'] ?></strong>. Chúng tôi sẽ xem xét và gỡ bỏ nội dung vi phạm trong vòng 24-48 giờ làm việc.</p>
                <?php endif; ?>
            </div>

        </div>
    <?php if (!$isAppView): ?>
    </div>
    <?php endif; ?>
</div>

<?php 
if (!$isAppView) {
    $footerPath = __DIR__ . "/themes/{$theme}/footer.php";
    if (file_exists($footerPath)) include $footerPath;
} else {
    echo '</body></html>';
}
?>
