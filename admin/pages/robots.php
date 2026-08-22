<?php
// admin/pages/robots.php
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

$robotsFile = __DIR__ . '/../../robots.txt';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_robots') {
    $content = $_POST['robots_content'] ?? '';
    
    // Validate if the file is writable or can be created
    if (file_put_contents($robotsFile, $content) !== false) {
        $successMsg = 'Đã lưu cấu hình Robots.txt thành công!';
    } else {
        $errorMsg = 'Không thể ghi file robots.txt. Vui lòng kiểm tra phân quyền (CHMOD 777) cho thư mục gốc.';
    }
}

// Read current content
$currentContent = "User-agent: *\nAllow: /\nSitemap: https://$_SERVER[HTTP_HOST]/sitemap.xml";
if (file_exists($robotsFile)) {
    $currentContent = file_get_contents($robotsFile);
}
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-white mb-2">Quản Lý Robots.txt</h2>
    <p class="text-sm text-gray-400">Định cấu hình tệp robots.txt để hướng dẫn các công cụ tìm kiếm (như Google, Bing) thu thập dữ liệu trên trang web của bạn.</p>
</div>

<?php if ($successMsg): ?>
    <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-lg flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($successMsg) ?>
    </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-lg flex items-center">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($errorMsg) ?>
    </div>
<?php endif; ?>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl p-6">
    <form method="POST">
        <input type="hidden" name="action" value="save_robots">
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Nội dung Robots.txt</label>
            <textarea 
                name="robots_content" 
                rows="12" 
                class="w-full bg-gray-950 border border-gray-800 rounded-lg px-4 py-3 text-gray-300 font-mono text-sm focus:ring-1 focus:ring-red-500 outline-none transition-all"
                placeholder="User-agent: *&#10;Allow: /"
            ><?= htmlspecialchars($currentContent) ?></textarea>
            <p class="mt-2 text-xs text-gray-500">Nếu tệp chưa tồn tại, hệ thống sẽ tự động tạo một tệp robots.txt ở thư mục gốc khi bạn bấm Lưu.</p>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium shadow-lg shadow-red-600/20 transition-all flex items-center">
                <i data-lucide="save" class="w-5 h-5 mr-2"></i> Lưu Cấu Hình
            </button>
        </div>
    </form>
</div>
