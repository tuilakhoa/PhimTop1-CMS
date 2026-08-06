<?php
$settings = getSettings();
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_comics') {
    $updates = [];
    $updates['comicApiUrl'] = trim($_POST['comicApiUrl'] ?? '');

    updateSettings($updates);
    $settings = getSettings();
    $successMsg = "Lưu cấu hình Truyện Tranh thành công!";
}
?>
<h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
    <i data-lucide="book-open" class="w-6 h-6 text-red-500"></i> Quản Lý Truyện Tranh
</h2>

<?php if ($successMsg): ?>
    <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl flex items-center shadow-lg">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
        <?= htmlspecialchars($successMsg) ?>
    </div>
<?php endif; ?>

<form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:p-8 shadow-xl max-w-4xl">
    <input type="hidden" name="action" value="save_comics">
    
    <div class="mb-5 bg-blue-500/10 border border-blue-500/30 p-4 rounded-xl flex items-start space-x-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm text-blue-300 font-medium mb-1">Cơ chế hoạt động:</p>
            <p class="text-xs text-blue-400/80">Plugin này sẽ tự động thay thế chức năng Đọc Truyện mặc định của CMS và chèn menu Truyện Tranh vào Header. Để tắt tính năng Truyện Tranh, bạn chỉ cần vô hiệu hóa Plugin này trong phần Quản Lý Plugin.</p>
        </div>
    </div>
    
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">Nguồn API Truyện (OTruyen)</label>
        <input type="url" name="comicApiUrl" value="<?= htmlspecialchars($settings['comicApiUrl'] ?? 'https://otruyenapi.com/v1/api') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow" required>
        <p class="text-xs text-gray-500 mt-2">URL cơ sở để tải dữ liệu truyện. Khuyên dùng mặc định: <code class="text-red-400">https://otruyenapi.com/v1/api</code></p>
    </div>

    <div class="mt-8 flex justify-end border-t border-gray-800 pt-6">
        <button type="submit" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg shadow-red-500/25 flex items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Lưu Thay Đổi
        </button>
    </div>
</form>
