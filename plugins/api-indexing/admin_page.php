<h2 class="text-2xl font-bold text-white mb-6">Tích Hợp Indexing API</h2>
<p class="text-gray-400 mb-6">Ping URL thủ công hoặc tự động tới Google Web Indexing API và IndexNow (Bing/Yandex) để lập chỉ mục bài viết ngay lập tức.</p>

<?php
$movies = [];
// Lấy phim từ API
$apiUrl = "https://phimapi.com/danh-sach/phim-moi-cap-nhat?page=1";
$json = @file_get_contents($apiUrl);
if ($json) {
    $data = json_decode($json, true);
    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            $movies[] = [
                'name' => $item['name'] ?? $item['title'] ?? '',
                'slug' => $item['slug'] ?? '',
                'updated_at' => $item['modified']['time'] ?? $item['updated_at'] ?? date('Y-m-d H:i:s')
            ];
        }
    }
}
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Cấu hình API -->
    <div>
        <form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-8">
            <input type="hidden" name="action" value="update_settings">
            
            <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-800 pb-2 flex items-center">
                <i data-lucide="key" class="w-5 h-5 mr-2 text-yellow-500"></i> Cấu Hình Khóa API
            </h3>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Google Service Account JSON</label>
                <textarea name="googleIndexJson" rows="5" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white font-mono text-xs focus:ring-1 focus:ring-blue-500 outline-none" placeholder='{"type": "service_account", "project_id": "..."}'><?= htmlspecialchars($settings['googleIndexJson'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-2">Dán toàn bộ nội dung file JSON được tạo từ Google Cloud Console.</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">IndexNow API Key</label>
                <input type="text" name="indexNowKey" value="<?= htmlspecialchars($settings['indexNowKey'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none" placeholder="Vd: 3a1b...9c2d">
                <p class="text-xs text-gray-500 mt-2">Chuỗi mã ngẫu nhiên tối thiểu 8 ký tự. Bạn có thể tự sinh một mã bất kỳ.</p>
            </div>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors flex items-center">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình
            </button>
        </form>
    </div>
    
    <!-- Test Push API -->
    <div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-800 pb-2 flex items-center">
                <i data-lucide="send" class="w-5 h-5 mr-2 text-green-500"></i> Bắn URL (Ping)
            </h3>
            
            <form action="<?= htmlspecialchars($settings['adminPath'] ?? '/admin') ?>/api/push_index.php" method="POST" target="pingIframe" class="mb-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Chọn phim để bắn (Gần đây nhất)</label>
                    <select name="url" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-green-500 outline-none">
                        <?php foreach($movies as $m): 
                            $slugMovie = $settings['slugMovie'] ?? 'phim';
                            $protocol = ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) ? 'http' : 'https';
                            $url = $protocol . "://$_SERVER[HTTP_HOST]/{$slugMovie}/" . $m['slug'];
                        ?>
                        <option value="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($m['name']) ?> (<?= date('d/m/Y H:i', strtotime($m['updated_at'])) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex space-x-4 mb-4">
                    <label class="flex items-center space-x-2 text-gray-300 cursor-pointer">
                        <input type="checkbox" name="pingGoogle" value="1" class="form-checkbox bg-gray-800 border-gray-700 text-green-500 rounded focus:ring-green-500" checked>
                        <span>Google Indexing</span>
                    </label>
                    <label class="flex items-center space-x-2 text-gray-300 cursor-pointer">
                        <input type="checkbox" name="pingIndexNow" value="1" class="form-checkbox bg-gray-800 border-gray-700 text-blue-500 rounded focus:ring-blue-500" checked>
                        <span>IndexNow (Bing)</span>
                    </label>
                </div>
                
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition-colors flex items-center w-full justify-center">
                    <i data-lucide="rocket" class="w-4 h-4 mr-2"></i> PUSH URL NOW
                </button>
            </form>
            
            <!-- Result Console -->
            <div class="bg-black rounded-lg border border-gray-800 p-4 relative">
                <span class="absolute top-0 right-0 bg-gray-800 text-[10px] text-gray-400 px-2 py-1 rounded-bl-lg font-mono">Console Output</span>
                <iframe name="pingIframe" class="w-full h-32 bg-transparent text-gray-300 font-mono text-xs outline-none" srcdoc='<body style="color:#aaa;font-family:monospace;font-size:12px;margin:0">Chờ lệnh Ping...</body>'></iframe>
            <!-- Cronjob Auto Push -->
            <div class="mt-8 bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-800 pb-2 flex items-center">
                    <i data-lucide="clock" class="w-5 h-5 mr-2 text-purple-500"></i> Tự Động Push (Cronjob)
                </h3>
                <p class="text-sm text-gray-400 mb-4 leading-relaxed">
                    Hệ thống đã hỗ trợ Tự động Ping khi có phim mới . 
                    Để kích hoạt, bạn hãy thêm dòng lệnh Cronjob sau vào hosting/VPS (chạy mỗi 15 hoặc 30 phút một lần):
                </p>
                <div class="bg-black rounded-lg border border-gray-800 p-4 font-mono text-sm text-green-400 overflow-x-auto">
                    <?php
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                    $cronUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/plugins/api-indexing/cron.php";
                    ?>
                    wget -qO- <?= htmlspecialchars($cronUrl) ?> &gt; /dev/null 2&gt;&amp;1
                </div>
                <p class="text-xs text-gray-500 mt-3">Cronjob sẽ kiểm tra danh sách phim mới nhất và tự động Push những URL chưa từng được Push.</p>
            </div>
            
        </div>
    </div>
</div>
