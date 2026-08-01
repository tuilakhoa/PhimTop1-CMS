<h2 class="text-2xl font-bold text-white mb-6">Quản Lý Sitemap</h2>

<?php
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$sitemapUrl = $baseUrl . '/sitemap.xml';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 relative">
            <input type="hidden" name="action" value="update_settings">
            
            <h3 class="text-lg font-medium text-white mb-4">Cấu Hình Sitemap</h3>
            
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Giới hạn TỔNG SỐ PHIM hiển thị</label>
                    <select name="sitemapLimit" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                        <option value="1000" <?= ($settings['sitemapLimit'] ?? 5000) == 1000 ? 'selected' : '' ?>>1,000 phim mới nhất</option>
                        <option value="5000" <?= ($settings['sitemapLimit'] ?? 5000) == 5000 ? 'selected' : '' ?>>5,000 phim mới nhất</option>
                        <option value="10000" <?= ($settings['sitemapLimit'] ?? 5000) == 10000 ? 'selected' : '' ?>>10,000 phim mới nhất</option>
                        <option value="20000" <?= ($settings['sitemapLimit'] ?? 5000) == 20000 ? 'selected' : '' ?>>20,000 phim mới nhất</option>
                        <option value="50000" <?= ($settings['sitemapLimit'] ?? 5000) == 50000 ? 'selected' : '' ?>>50,000 phim mới nhất (Không khuyến nghị)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Số lượng Link / 1 File Sitemap</label>
                    <select name="sitemapLinksPerFile" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                        <option value="500" <?= ($settings['sitemapLinksPerFile'] ?? 1000) == 500 ? 'selected' : '' ?>>500 Link</option>
                        <option value="1000" <?= ($settings['sitemapLinksPerFile'] ?? 1000) == 1000 ? 'selected' : '' ?>>1,000 Link</option>
                        <option value="5000" <?= ($settings['sitemapLinksPerFile'] ?? 1000) == 5000 ? 'selected' : '' ?>>5,000 Link</option>
                        <option value="10000" <?= ($settings['sitemapLinksPerFile'] ?? 1000) == 10000 ? 'selected' : '' ?>>10,000 Link</option>
                    </select>
                </div>
            </div>

            <div class="mb-6 space-y-4">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="hidden" name="sitemapIncludeMovies" value="0">
                    <input type="checkbox" name="sitemapIncludeMovies" value="1" <?= ($settings['sitemapIncludeMovies'] ?? 1) ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                    <span class="text-gray-300 text-sm font-medium">Bao gồm danh sách Phim</span>
                </label>
                
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="hidden" name="sitemapIncludeCategories" value="0">
                    <input type="checkbox" name="sitemapIncludeCategories" value="1" <?= ($settings['sitemapIncludeCategories'] ?? 1) ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                    <span class="text-gray-300 text-sm font-medium">Bao gồm Thể Loại & Quốc Gia</span>
                </label>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors flex items-center shadow-lg shadow-blue-500/20">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình
            </button>
        </form>
    </div>

    <div class="lg:col-span-1 space-y-6">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-lg font-medium text-white mb-4">Liên Kết Sitemap</h3>
            <div class="bg-gray-800 p-3 rounded-lg flex items-center justify-between mb-4 overflow-hidden">
                <span class="text-gray-300 text-sm truncate" id="sitemap-link"><?= $sitemapUrl ?></span>
                <button onclick="navigator.clipboard.writeText('<?= $sitemapUrl ?>'); alert('Đã copy!')" class="text-gray-400 hover:text-white ml-2 flex-shrink-0" title="Copy Link">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
            </div>
            <a href="<?= $sitemapUrl ?>" target="_blank" class="w-full flex items-center justify-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition-colors border border-gray-700">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Mở Sitemap</span>
            </a>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-lg font-medium text-white mb-4">Công Cụ Ping</h3>
            <p class="text-xs text-gray-400 mb-4">Gửi thông báo tới các bộ máy tìm kiếm rằng sitemap của bạn vừa được cập nhật.</p>
            
            <div class="space-y-3">
                <a href="https://www.google.com/ping?sitemap=<?= urlencode($sitemapUrl) ?>" target="_blank" class="w-full flex items-center justify-center space-x-2 bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-colors">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Ping Google</span>
                </a>
                <a href="https://www.bing.com/ping?sitemap=<?= urlencode($sitemapUrl) ?>" target="_blank" class="w-full flex items-center justify-center space-x-2 bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-colors">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Ping Bing</span>
                </a>
            </div>
        </div>
    </div>
</div>
