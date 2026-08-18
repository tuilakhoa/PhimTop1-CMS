<h2 class="text-2xl font-bold text-white mb-6">Cấu Hình Chung</h2>

<div class="mb-6 border-b border-gray-800">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs" id="settings-tabs">
        <button type="button" onclick="switchTab('general')" class="tab-btn active-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="general">
            <i data-lucide="settings" class="w-4 h-4 inline-block mr-2"></i>Cơ Bản
        </button>
        <button type="button" onclick="switchTab('footer')" class="tab-btn inactive-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="footer">
            <i data-lucide="layout-template" class="w-4 h-4 inline-block mr-2"></i>Footer & Mạng Xã Hội
        </button>
        <button type="button" onclick="switchTab('database')" class="tab-btn inactive-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="database">
            <i data-lucide="database" class="w-4 h-4 inline-block mr-2"></i>Cơ Sở Dữ Liệu
        </button>
        <button type="button" onclick="switchTab('theme')" class="tab-btn inactive-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="theme">
            <i data-lucide="monitor" class="w-4 h-4 inline-block mr-2"></i>Giao Diện Trang Chủ
        </button>
    </nav>
</div>

<form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-3xl relative">
    <input type="hidden" name="action" value="update_settings">
    
    <!-- Tab 1: General -->
    <div id="tab-general" class="tab-content block animate-fade-in">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Chế độ nguồn dữ liệu (Display Mode)</label>
            <select name="displayMode" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <option value="api" <?= $settings['displayMode'] === 'api' ? 'selected' : '' ?>>Gọi API Trực Tiếp (Khuyên dùng nếu lười cào)</option>
                <option value="crawl" <?= $settings['displayMode'] === 'crawl' ? 'selected' : '' ?>>Đọc Từ Database MySQL (Yêu cầu phải cào phim)</option>
            </select>
            <p class="text-xs text-gray-500 mt-2">Chế độ "Database MySQL" giúp load cực nhanh và an toàn khi API gốc chết, nhưng bạn phải vào mục Cào Phim mỗi ngày.</p>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Nguồn API (Áp dụng khi dùng "Gọi API Trực Tiếp")</label>
            <select name="apiSource" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <option value="kkphim" <?= (!isset($settings['apiSource']) || $settings['apiSource'] === 'kkphim') ? 'selected' : '' ?>>KKPhim (phimapi.com)</option>
                <option value="ophim" <?= (isset($settings['apiSource']) && $settings['apiSource'] === 'ophim') ? 'selected' : '' ?>>Ophim (ophim1.com)</option>
                <option value="nguonc" <?= (isset($settings['apiSource']) && $settings['apiSource'] === 'nguonc') ? 'selected' : '' ?>>NguonC (phim.nguonc.com)</option>
            </select>
            <p class="text-xs text-gray-500 mt-2">Hệ thống sẽ tự động điều chỉnh cấu trúc dữ liệu để giao diện hoạt động bình thường trên mọi nguồn.</p>
        </div>



        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">TMDB API Key</label>
            <input type="text" name="tmdbApiKey" value="<?= htmlspecialchars($settings['tmdbApiKey'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            <p class="text-xs text-gray-500 mt-2">API Key để lấy trực tiếp ảnh từ TMDB khi CDN bị lỗi. Khuyên dùng: <code class="text-red-400">b775c363e46a24e8c885479b0131c4d2</code></p>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Cho phép Tự động Cập nhật (Auto Update)</label>
            <select name="allowAutoUpdate" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <option value="1" <?= (!isset($settings['allowAutoUpdate']) || $settings['allowAutoUpdate'] == 1) ? 'selected' : '' ?>>Bật (Cho phép tải code mới từ Github)</option>
                <option value="0" <?= (isset($settings['allowAutoUpdate']) && $settings['allowAutoUpdate'] == 0) ? 'selected' : '' ?>>Tắt (Khoá cập nhật để bảo vệ code tuỳ biến)</option>
            </select>
            <p class="text-xs text-gray-500 mt-2">Nếu bạn sửa mã nguồn riêng, hãy <b>TẮT</b> tính năng này để tránh bị ghi đè code khi có bản cập nhật mới.</p>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Tính năng Xem Tiếp (Continue Watching)</label>
            <select name="enableContinueWatching" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow mb-4">
                <option value="1" <?= (!isset($settings['enableContinueWatching']) || $settings['enableContinueWatching'] == 1) ? 'selected' : '' ?>>Bật (Cho phép lưu lịch sử và xem tiếp)</option>
                <option value="0" <?= (isset($settings['enableContinueWatching']) && $settings['enableContinueWatching'] == 0) ? 'selected' : '' ?>>Tắt</option>
            </select>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Quản lý người dùng đang xem (Watching Sessions)</label>
            <select name="enableWatchingSession" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow mb-4">
                <option value="1" <?= (!isset($settings['enableWatchingSession']) || $settings['enableWatchingSession'] == 1) ? 'selected' : '' ?>>Bật (Cho phép theo dõi và điều khiển)</option>
                <option value="0" <?= (isset($settings['enableWatchingSession']) && $settings['enableWatchingSession'] == 0) ? 'selected' : '' ?>>Tắt (Không theo dõi để giảm tải cho CSDL)</option>
            </select>
            
            <label class="block text-sm font-medium text-gray-300 mb-2">Đối tượng theo dõi</label>
            <select name="trackAnonymousSession" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <option value="0" <?= (!isset($settings['trackAnonymousSession']) || $settings['trackAnonymousSession'] == 0) ? 'selected' : '' ?>>Chỉ theo dõi người dùng đã đăng nhập (Giảm tải Database)</option>
                <option value="1" <?= (isset($settings['trackAnonymousSession']) && $settings['trackAnonymousSession'] == 1) ? 'selected' : '' ?>>Theo dõi tất cả (Bao gồm Khách/Ẩn danh)</option>
            </select>
            <p class="text-xs text-gray-500 mt-2">Tính năng này giúp giới hạn lượng dữ liệu ghi vào Database. Khuyến nghị chỉ theo dõi người dùng đã đăng nhập.</p>
        </div>
    </div>

    <!-- Tab Theme Home -->
    <div id="tab-theme" class="tab-content hidden animate-fade-in">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Nguồn Phim Nổi Bật</label>
            <select name="featuredType" id="featuredTypeSelect" onchange="toggleFeaturedOptions()" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <option value="latest" <?= ($settings['featuredType'] ?? 'latest') === 'latest' ? 'selected' : '' ?>>Phim Mới Nhất (Tự động lấy phim vừa cập nhật)</option>
                <option value="view" <?= ($settings['featuredType'] ?? 'latest') === 'view' ? 'selected' : '' ?>>Xem Nhiều Nhất (Theo lượt xem Database local)</option>
                <option value="admin" <?= ($settings['featuredType'] ?? 'latest') === 'admin' ? 'selected' : '' ?>>Admin Chọn Thủ Công (Nhập Slug)</option>
            </select>
        </div>

        <div class="mb-6" id="featuredSlugWrapper" style="<?= ($settings['featuredType'] ?? 'latest') === 'admin' ? '' : 'display: none;' ?>">
            <label class="block text-sm font-medium text-gray-300 mb-2">Slug Phim Nổi Bật</label>
            <input type="text" name="featuredMovieSlug" value="<?= htmlspecialchars($settings['featuredMovieSlug'] ?? '') ?>" placeholder="vd: mai, dao-pho-kem-pho" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            <p class="text-xs text-gray-500 mt-2">Nhập slug của phim. Có thể nhập nhiều slug cách nhau bởi dấu phẩy nếu muốn dùng Slider.</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Kiểu Hiển Thị Hero Banner</label>
            <select name="featuredStyle" id="featuredStyleSelect" onchange="toggleFeaturedOptions()" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <option value="single" <?= ($settings['featuredStyle'] ?? 'single') === 'single' ? 'selected' : '' ?>>Ảnh Đơn (Banner Tĩnh)</option>
                <option value="slider" <?= ($settings['featuredStyle'] ?? 'single') === 'slider' ? 'selected' : '' ?>>Slider Động (Swiper)</option>
            </select>
        </div>

        <div class="mb-6" id="featuredCountWrapper" style="<?= ($settings['featuredStyle'] ?? 'single') === 'slider' ? '' : 'display: none;' ?>">
            <label class="block text-sm font-medium text-gray-300 mb-2">Số lượng phim trong Slider</label>
            <input type="number" min="1" max="10" name="featuredCount" value="<?= htmlspecialchars($settings['featuredCount'] ?? 5) ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            <p class="text-xs text-gray-500 mt-2">Chỉ áp dụng khi chọn Nguồn là "Mới Nhất" hoặc "Xem Nhiều Nhất" và Hiển thị là "Slider".</p>
        </div>
    </div>

    <!-- Tab 2: Footer -->
    <div id="tab-footer" class="tab-content hidden animate-fade-in">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Đoạn văn bản giới thiệu Footer</label>
            <textarea name="footerText" rows="3" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow" placeholder="Nhập giới thiệu ngắn gọn ở footer..."><?= htmlspecialchars($settings['footerText'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1"><i data-lucide="facebook" class="w-4 h-4 inline mr-1 text-blue-500"></i>Facebook URL</label>
                <input type="text" name="socialFacebook" value="<?= htmlspecialchars($settings['socialFacebook'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1"><i data-lucide="youtube" class="w-4 h-4 inline mr-1 text-red-500"></i>Youtube URL</label>
                <input type="text" name="socialYoutube" value="<?= htmlspecialchars($settings['socialYoutube'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1"><i data-lucide="twitter" class="w-4 h-4 inline mr-1 text-blue-400"></i>X (Twitter) URL</label>
                <input type="text" name="socialTwitter" value="<?= htmlspecialchars($settings['socialTwitter'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1"><i data-lucide="send" class="w-4 h-4 inline mr-1 text-blue-400"></i>Telegram URL</label>
                <input type="text" name="socialTelegram" value="<?= htmlspecialchars($settings['socialTelegram'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            </div>
        </div>
    </div>
    

    <!-- Tab 4: Database -->
    <?php $dbConfig = getDbConfig() ?: []; $isFs = ($dbConfig['type'] ?? 'mysql') === 'firestore'; ?>
    <div id="tab-database" class="tab-content hidden animate-fade-in">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Loại Database</label>
            <select name="dbType" onchange="document.getElementById('db-mysql').classList.toggle('hidden', this.value==='firestore'); document.getElementById('db-firestore').classList.toggle('hidden', this.value==='mysql');" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <option value="mysql" <?= !$isFs ? 'selected' : '' ?>>MySQL</option>
                <option value="firestore" <?= $isFs ? 'selected' : '' ?>>Firebase Firestore (NoSQL)</option>
            </select>
        </div>

        <div id="db-mysql" class="space-y-4 <?= $isFs ? 'hidden' : '' ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Host</label>
                    <input type="text" name="dbHost" value="<?= htmlspecialchars($dbConfig['host'] ?? '127.0.0.1') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Tên Database</label>
                    <input type="text" name="dbName" value="<?= htmlspecialchars($dbConfig['database'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Username</label>
                    <input type="text" name="dbUser" value="<?= htmlspecialchars($dbConfig['user'] ?? 'root') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                    <input type="password" name="dbPass" value="<?= htmlspecialchars($dbConfig['password'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none">
                </div>
            </div>
        </div>

        <div id="db-firestore" class="space-y-4 <?= !$isFs ? 'hidden' : '' ?>">
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-4">
                <h4 class="text-blue-400 font-semibold mb-2 flex items-center"><i data-lucide="shield" class="w-4 h-4 mr-2"></i>Bảo Mật Firestore:</h4>
                <ol class="list-decimal list-inside text-sm text-gray-300 space-y-1">
                    <li>Vào mục <b>Firestore Database</b> > <b>Rules</b> và xuất bản đoạn Rules sau:
<pre class="bg-gray-900 border border-gray-700 p-3 mt-2 rounded text-xs overflow-x-auto text-blue-300 font-mono">rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /{document=**} {
      allow read, write: if false; // Chỉ có quyền đọc ghi thông qua PHP Server
    }
  }
}</pre>
                    </li>
                </ol>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-800 space-y-4">
            <h3 class="text-lg font-semibold text-white mb-2 flex items-center"><i data-lucide="key" class="w-5 h-5 mr-2 text-yellow-500"></i> Cấu Hình Google Cloud (Service Account)</h3>
            <p class="text-xs text-gray-500 mb-4">Tài khoản dịch vụ này được sử dụng chung cho kết nối <b>Firestore Database</b> và API của <b>Google Analytics</b>.</p>
            
            <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4 mb-4">
                <h4 class="text-yellow-400 font-semibold mb-2 flex items-center"><i data-lucide="info" class="w-4 h-4 mr-2"></i>Hướng dẫn lấy Service Account JSON:</h4>
                <ol class="list-decimal list-inside text-sm text-gray-300 space-y-1">
                    <li>Truy cập <a href="https://console.firebase.google.com/" target="_blank" class="text-yellow-400 hover:underline">Firebase Console</a> và tạo/chọn Project.</li>
                    <li>Vào <b>Project settings</b> (Cài đặt dự án) > <b>Service accounts</b> (Tài khoản dịch vụ).</li>
                    <li>Bấm <b>Generate new private key</b> (Tạo khoá mới) để tải file JSON về máy.</li>
                    <li>Mở file JSON vừa tải, copy <b>toàn bộ nội dung</b> và dán vào ô <b>Service Account JSON</b> bên dưới.</li>
                    <li><b>Project ID</b> chính là giá trị của mục <code class="text-red-400 bg-gray-800 px-1 rounded">project_id</code> nằm bên trong nội dung file JSON đó.</li>
                </ol>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Project ID</label>
                <input type="text" name="projectId" value="<?= htmlspecialchars($dbConfig['projectId'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Service Account JSON (Nội dung file)</label>
                <textarea name="serviceAccount" rows="5" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white outline-none custom-scrollbar font-mono text-sm" placeholder='{"type": "service_account", ...}'><?= htmlspecialchars(json_encode($dbConfig['serviceAccount'] ?? new stdClass(), JSON_PRETTY_PRINT)) ?></textarea>
            </div>
        </div>
    </div>

    <div class="mt-8 pt-6 border-t border-gray-800">
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-6 rounded-lg transition-all shadow-lg shadow-red-600/20 flex items-center transform hover:-translate-y-0.5">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình
        </button>
    </div>
</form>

<style>
    .active-tab {
        border-color: #ef4444; /* red-500 */
        color: #ef4444;
    }
    .inactive-tab {
        border-color: transparent;
        color: #9ca3af; /* gray-400 */
    }
    .inactive-tab:hover {
        border-color: #374151; /* gray-700 */
        color: #d1d5db; /* gray-300 */
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    function switchTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.remove('block');
            el.classList.add('hidden');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active-tab');
            el.classList.add('inactive-tab');
        });
        
        // Show selected tab content
        const content = document.getElementById('tab-' + tabId);
        if (content) {
            content.classList.remove('hidden');
            content.classList.add('block');
        }
        
        // Add active class to selected button
        const btn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        if (btn) {
            btn.classList.remove('inactive-tab');
            btn.classList.add('active-tab');
        }
    }

    function toggleFeaturedOptions() {
        const typeSelect = document.getElementById('featuredTypeSelect');
        const styleSelect = document.getElementById('featuredStyleSelect');
        const slugWrapper = document.getElementById('featuredSlugWrapper');
        const countWrapper = document.getElementById('featuredCountWrapper');
        
        if (typeSelect.value === 'admin') {
            slugWrapper.style.display = 'block';
        } else {
            slugWrapper.style.display = 'none';
        }
        
        if (styleSelect.value === 'slider') {
            countWrapper.style.display = 'block';
        } else {
            countWrapper.style.display = 'none';
        }
    }
</script>
