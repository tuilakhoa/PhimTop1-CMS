<?php
requireAdmin();
$settings = getSettings();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_app_settings') {
    $appApiKey = trim($_POST['appApiKey'] ?? '');
    $appBannerEnabled = isset($_POST['appBannerEnabled']) ? 1 : 0;
    $appDownloadUrl = trim($_POST['appDownloadUrl'] ?? '');
    $appDownloadUrlTv = trim($_POST['appDownloadUrlTv'] ?? '');
    
    updateSettings([
        'appApiKey' => $appApiKey,
        'appBannerEnabled' => $appBannerEnabled,
        'appDownloadUrl' => $appDownloadUrl,
        'appDownloadUrlTv' => $appDownloadUrlTv
    ]);
    
    $success = "Cập nhật cấu hình App thành công!";
    $settings = getSettings(); // Refresh
}
?>

<h2 class="text-2xl font-bold text-white mb-6">Cấu Hình Kết Nối App (API)</h2>

<?php if ($success): ?>
    <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-lg flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="max-w-4xl space-y-6">
    <!-- Top Block: Form -->
    <form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 relative shadow-sm">
        <input type="hidden" name="action" value="update_app_settings">
            
            <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2">Thông tin bảo mật API</h3>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">App API Key</label>
                <div class="flex">
                    <input type="text" id="appApiKey" name="appApiKey" value="<?= htmlspecialchars($settings['appApiKey'] ?? '') ?>" placeholder="Chưa có mã bảo mật" class="w-full bg-gray-800 border border-gray-700 rounded-l-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none transition-shadow" readonly>
                    <button type="button" onclick="generateApiKey()" class="bg-gray-700 hover:bg-gray-600 border border-gray-700 border-l-0 rounded-r-lg px-4 py-2.5 text-sm font-medium text-white transition-colors flex-shrink-0" title="Tạo mã ngẫu nhiên">
                        <i data-lucide="refresh-cw" class="w-4 h-4 inline-block mr-1"></i> Tạo Mã Mới
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Mã này được hệ thống tạo ngẫu nhiên nhằm bảo mật các kết nối từ Native App tới hệ thống API. Bạn không thể tự nhập để tránh lộ lọt.</p>
            </div>

            <div class="mb-6 border-t border-gray-800 pt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Link Tải App Mobile (APK / Play Store)</label>
                <input type="text" name="appDownloadUrl" value="<?= htmlspecialchars($settings['appDownloadUrl'] ?? '') ?>" placeholder="https://..." class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <p class="text-xs text-gray-500 mt-2">Đường dẫn tải ứng dụng Mobile. Nếu để trống, nút tải app trên web sẽ không hiển thị.</p>
            </div>

            <div class="mb-6 border-t border-gray-800 pt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Link Tải App Android TV (APK)</label>
                <input type="text" name="appDownloadUrlTv" value="<?= htmlspecialchars($settings['appDownloadUrlTv'] ?? '') ?>" placeholder="https://..." class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <p class="text-xs text-gray-500 mt-2">Đường dẫn tải ứng dụng dành cho Smart TV. Nếu để trống, nút tải app TV trên web sẽ không hiển thị.</p>
            </div>

            <div class="mb-6 border-t border-gray-800 pt-6">
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="appBannerEnabled" value="1" class="sr-only" <?= (!empty($settings['appBannerEnabled']) ? 'checked' : '') ?>>
                        <div class="block bg-gray-700 w-10 h-6 rounded-full checkbox-bg"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition checkbox-dot"></div>
                    </div>
                    <div class="ml-3 text-sm font-medium text-gray-300">
                        Bật gợi ý tải App trên Web (Smart App Banner)
                        <p class="text-xs text-gray-500 mt-1 font-normal">Khi người dùng truy cập web bằng trình duyệt điện thoại, một nút "Mở trong App" sẽ hiện ra.</p>
                    </div>
                </label>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-800">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-6 rounded-lg transition-all shadow-lg shadow-red-600/20 flex items-center transform hover:-translate-y-0.5">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình
                </button>
            </div>
        </form>
    </form>

    <!-- Bottom Block: API Guide -->
    <?php
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || $_SERVER['SERVER_PORT'] == 443 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $baseUrl = ($isHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    ?>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2">Hướng dẫn kết nối API</h3>
            
            <p class="text-sm text-gray-400 mb-4">Native App có thể gọi dữ liệu từ các Endpoint sau. <br><span class="text-yellow-400">Lưu ý:</span> Để test trực tiếp trên trình duyệt, bạn cần nối thêm App API Key vào cuối link bằng cú pháp <code class="bg-gray-800 px-1 rounded">?key=MÃ_BẢO_MẬT</code> (nếu link chưa có dấu ?) hoặc <code class="bg-gray-800 px-1 rounded">&key=MÃ_BẢO_MẬT</code> (nếu link đã có dấu ?). Đối với phương thức POST, vui lòng gửi dữ liệu dưới dạng JSON (Raw Body) hoặc gửi Token qua Header <code>Authorization: Bearer [TOKEN]</code>.<br>Ví dụ: <code class="text-white bg-gray-800 px-1 rounded"><?= $baseUrl . '/api/v1/search.php?keyword=batman&key=' . htmlspecialchars($settings['appApiKey'] ?: 'MÃ_CỦA_BẠN') ?></code></p>
            
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Khởi tạo (Init) <span class="text-gray-500 font-normal">- GET</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    <?= $baseUrl . '/api/v1/app_init.php' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Trang Chủ (Home) <span class="text-gray-500 font-normal">- GET</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    <?= $baseUrl . '/api/v1/home.php?page=1' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Chi Tiết Phim <span class="text-gray-500 font-normal">- GET</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    <?= $baseUrl . '/api/v1/movie.php?slug=ten-phim' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Lấy Toàn Bộ Thể Loại <span class="text-gray-500 font-normal">- GET</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    <?= $baseUrl . '/api/v1/categories.php' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Danh sách Phim theo Thể Loại / Quốc Gia <span class="text-gray-500 font-normal">- GET</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    <?= $baseUrl . '/api/v1/category.php?type=the-loai&slug=hanh-dong&page=1' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Tìm Kiếm <span class="text-gray-500 font-normal">- GET</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    <?= $baseUrl . '/api/v1/search.php?keyword=batman&page=1' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Xác Thực (Login/Register) <span class="text-gray-500 font-normal">- POST</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    <?= $baseUrl . '/api/v1/auth.php?action=login' ?><br>
                    <?= $baseUrl . '/api/v1/auth.php?action=register' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Theo Dõi (Follow) <span class="text-gray-500 font-normal">- GET/POST (Cần Auth Header)</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    GET: <?= $baseUrl . '/api/v1/follow.php?action=list&type=movie' ?><br>
                    GET: <?= $baseUrl . '/api/v1/follow.php?action=check&slug=ten-phim' ?><br>
                    POST: <?= $baseUrl . '/api/v1/follow.php?action=toggle' ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Bình Luận (Comments) <span class="text-gray-500 font-normal">- GET/POST</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    GET: <?= $baseUrl . '/api/v1/comments.php?slug=ten-phim' ?><br>
                    POST: <?= $baseUrl . '/api/v1/comments.php' ?> (Cần Auth Header)
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-300 mb-1">API Thông Báo (Notifications) <span class="text-gray-500 font-normal">- GET/POST (Cần Auth Header)</span>:</label>
                <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                    GET: <?= $baseUrl . '/api/v1/notifications.php?action=list' ?><br>
                    POST: <?= $baseUrl . '/api/v1/notifications.php?action=mark_read' ?>
                </div>
            </div>
        </div>
        
        <div class="mt-4 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <div class="flex">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-500 mr-2 flex-shrink-0 mt-0.5"></i>
                    <div class="text-xs text-yellow-500/90 leading-relaxed">
                        <b class="text-yellow-400">Hướng dẫn cấu hình App:</b><br>
                        <ol class="list-decimal list-inside space-y-2 mt-2">
                            <li>Copy <b>App API Key</b> ở bên trên và dán vào biến <code class="bg-yellow-500/20 px-1 py-0.5 rounded text-yellow-400 font-mono">API_KEY</code> trong class RetrofitClient tại file:
                                <div class="mt-1 bg-black/30 p-2 rounded text-[10.5px] text-gray-300 font-mono break-all border border-yellow-500/10">android-app/app/src/main/java/com/phimtop1/app/api/CmsApi.kt</div>
                            </li>
                            <li>Copy tên miền <code class="bg-yellow-500/20 px-1 py-0.5 rounded text-yellow-400 font-mono"><?= $baseUrl . '/' ?></code> và dán vào biến <code class="bg-yellow-500/20 px-1 py-0.5 rounded text-yellow-400 font-mono">BASE_URL</code> trong class RetrofitClient tại cùng file trên.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
    </div>
</div>

<style>
    /* Toggle switch CSS */
    input:checked ~ .checkbox-bg {
        background-color: #ef4444; /* red-500 */
    }
    input:checked ~ .checkbox-dot {
        transform: translateX(100%);
    }
</style>

<script>
    function generateApiKey() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = 'pt1_';
        for (let i = 0; i < 32; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('appApiKey').value = result;
        
        // Add a subtle highlight effect to show it changed
        const input = document.getElementById('appApiKey');
        input.classList.add('ring-2', 'ring-red-500');
        setTimeout(() => {
            input.classList.remove('ring-2', 'ring-red-500');
        }, 500);
    }
</script>
