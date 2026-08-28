<?php
require_once __DIR__ . '/includes/db.php';
$settings = getSettings();
$theme = $settings['theme'] ?? 'phimhayok';

$themePrivacyFile = __DIR__ . "/themes/{$theme}/privacy.php";
if (file_exists($themePrivacyFile)) {
    require_once $themePrivacyFile;
} else {
    // Basic fallback HTML
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chính sách bảo mật - <?php echo htmlspecialchars($settings['site_name'] ?? 'PhimTop1'); ?></title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
            .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            h1, h2, h3 { color: #2c3e50; }
            a { color: #3498db; text-decoration: none; }
            a:hover { text-decoration: underline; }
            @media (prefers-color-scheme: dark) {
                body { background-color: #1a1a1a; color: #f4f4f4; }
                .container { background-color: #2c2c2c; }
                h1, h2, h3 { color: #e0e0e0; }
                a { color: #64b5f6; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Chính sách bảo mật (Privacy Policy)</h1>
            <p>Cập nhật lần cuối: <?php echo date('d/m/Y'); ?></p>

            <h2>1. Thu thập dữ liệu và thông tin cá nhân</h2>
            <p>Chúng tôi có thể thu thập các loại thông tin sau để cung cấp và cải thiện dịch vụ:</p>
            <ul>
                <li><strong>Thông tin cá nhân (Personal Data):</strong> Khi bạn đăng ký, chúng tôi có thể yêu cầu địa chỉ email, tên hiển thị, và ảnh đại diện (nếu bạn sử dụng đăng nhập qua Google hoặc Apple).</li>
                <li><strong>Dữ liệu sử dụng (Usage Data):</strong> Lịch sử xem phim, danh sách phim yêu thích (playlist), thời gian truy cập, loại thiết bị, hệ điều hành và thông tin sự cố (crash logs) để cải thiện hiệu năng ứng dụng.</li>
                <li><strong>Cookie và công cụ theo dõi:</strong> Chúng tôi sử dụng Cookie (đối với bản Web) và các mã thông báo tương tự để duy trì phiên đăng nhập và ghi nhớ tùy chọn của bạn.</li>
            </ul>

            <h2>2. Mục đích sử dụng dữ liệu</h2>
            <p>Chúng tôi sử dụng dữ liệu thu thập được cho các mục đích sau:</p>
            <ul>
                <li>Cung cấp, duy trì và cá nhân hóa trải nghiệm ứng dụng (ví dụ: gợi ý phim, đồng bộ hóa lịch sử xem giữa các thiết bị).</li>
                <li>Cải thiện chất lượng dịch vụ, phân tích lỗi (bug) và tối ưu hiệu suất thông qua dữ liệu phân tích ẩn danh.</li>
                <li>Xác thực người dùng và bảo mật tài khoản.</li>
            </ul>

            <h2>3. Chia sẻ thông tin với bên thứ ba</h2>
            <p>Chúng tôi cam kết không bán, cho thuê hoặc chia sẻ dữ liệu cá nhân của bạn cho các bên thứ ba vì mục đích tiếp thị. Tuy nhiên, chúng tôi có thể sử dụng các dịch vụ của bên thứ ba để hỗ trợ vận hành, bao gồm:</p>
            <ul>
                <li><strong>Google Play Services & Firebase Analytics:</strong> Để theo dõi sự cố (Crashlytics) và phân tích hành vi người dùng nhằm cải thiện ứng dụng.</li>
                <li><strong>Dịch vụ quảng cáo (Ad Networks):</strong> Ứng dụng có thể hiển thị quảng cáo qua các mạng quảng cáo. Các nhà cung cấp này có thể sử dụng dữ liệu thiết bị (không bao gồm tên hay email) để hiển thị quảng cáo phù hợp.</li>
            </ul>
            <p>Các bên thứ ba này có Chính sách bảo mật riêng và chúng tôi khuyến khích bạn tham khảo chính sách của họ.</p>

            <h2>4. Quyền của người dùng và Xóa dữ liệu</h2>
            <p>Bạn có toàn quyền kiểm soát dữ liệu của mình:</p>
            <ul>
                <li><strong>Truy cập và Chỉnh sửa:</strong> Bạn có thể cập nhật thông tin cá nhân trực tiếp trong phần "Cài đặt tài khoản".</li>
                <li><strong>Quyền yêu cầu xóa (Data Deletion Request):</strong> Theo yêu cầu của các cửa hàng ứng dụng (Google Play, App Store), người dùng có thể xóa hoàn toàn tài khoản và dữ liệu liên quan. Bạn có thể sử dụng tính năng "Xóa tài khoản" ngay trong ứng dụng, hoặc gửi yêu cầu trực tiếp thông qua thông tin liên hệ ở bên dưới. Khi tài khoản bị xóa, toàn bộ dữ liệu cá nhân, lịch sử và playlist của bạn sẽ bị xóa vĩnh viễn khỏi hệ thống của chúng tôi.</li>
            </ul>

            <h2>5. Quyền riêng tư của trẻ em (Children's Privacy)</h2>
            <p>Dịch vụ của chúng tôi không hướng trực tiếp đến trẻ em dưới 13 tuổi. Chúng tôi không cố ý thu thập thông tin nhận dạng cá nhân từ trẻ em dưới 13 tuổi. Nếu bạn là cha mẹ hoặc người giám hộ và biết rằng con bạn đã cung cấp Dữ liệu Cá nhân cho chúng tôi, vui lòng liên hệ ngay với chúng tôi để tiến hành xóa bỏ những thông tin đó (Tuân thủ đạo luật COPPA).</p>

            <h2>6. Bảo mật dữ liệu</h2>
            <p>Chúng tôi áp dụng các tiêu chuẩn mã hóa cho việc truyền tải dữ liệu và lưu trữ an toàn mật khẩu (đã băm - hashed) cũng như thông tin nhạy cảm. Dù chúng tôi luôn cố gắng bảo vệ tốt nhất, không có phương thức truyền tải qua Internet nào là an toàn tuyệt đối 100%.</p>

            <h2>7. Thay đổi Chính sách bảo mật</h2>
            <p>Chúng tôi có thể cập nhật Chính sách bảo mật này theo thời gian để phản ánh các thay đổi trong luật pháp hoặc tính năng của ứng dụng. Bạn nên kiểm tra định kỳ trang này. Những thay đổi sẽ có hiệu lực ngay khi được đăng tải.</p>

            <h2>8. Thông tin liên hệ</h2>
            <p>Nếu bạn có bất kỳ câu hỏi nào về Chính sách bảo mật này, yêu cầu hỗ trợ hoặc yêu cầu xóa dữ liệu, vui lòng liên hệ với chúng tôi qua email: <strong>support@<?php echo $_SERVER['HTTP_HOST'] ?? 'phimtop1.com'; ?></strong></p>

            <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px; text-align: center; font-size: 0.9em; color: #777;">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'PhimTop1'); ?>. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
