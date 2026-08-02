<h2 class="text-2xl font-bold text-white mb-6">Cấu Hình SEO & Thương Hiệu</h2>

<div class="mb-6 flex space-x-1 border-b border-gray-800">
    <button type="button" onclick="switchTab('tab-seo')" id="btn-tab-seo" class="px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-500 transition-colors">SEO & Cơ Bản</button>
    <button type="button" onclick="switchTab('tab-router')" id="btn-tab-router" class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-300 transition-colors">Định tuyến (Router)</button>
    <button type="button" onclick="switchTab('tab-verify')" id="btn-tab-verify" class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-300 transition-colors">Xác Minh (Webmaster)</button>
    <button type="button" onclick="switchTab('tab-code')" id="btn-tab-code" class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-300 transition-colors">Mã Tùy Chỉnh (Code)</button>
</div>

<form method="POST" enctype="multipart/form-data" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-2xl relative">
    <input type="hidden" name="action" value="update_settings">
    
    <!-- TAB 1: SEO & CƠ BẢN -->
    <div id="tab-seo" class="tab-content block">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Tên Website (Site Name)</label>
            <input type="text" name="siteName" value="<?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Logo Website & Favicon</label>
            <?php if (!empty($settings['logoUrl'])): ?>
                <div class="mb-3">
                    <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" class="h-12 object-contain bg-gray-800 p-2 rounded border border-gray-700" alt="Current Logo">
                </div>
            <?php endif; ?>
            <input type="file" name="logoFile" accept="image/*" class="w-full text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
            <p class="text-xs text-gray-500 mt-2">Tải lên file ảnh (PNG, JPG, WEBP). Khuyên dùng nền trong suốt.</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Tiêu Đề Trang Chủ (SEO Title)</label>
            <input type="text" name="seoTitle" value="<?= htmlspecialchars($settings['seoTitle'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Mô Tả SEO (Meta Description)</label>
            <textarea name="seoDesc" rows="3" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none"><?= htmlspecialchars($settings['seoDesc'] ?? '') ?></textarea>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-300 mb-2">Từ Khóa SEO (Meta Keywords)</label>
            <input type="text" name="seoKeywords" value="<?= htmlspecialchars($settings['seoKeywords'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none">
            <p class="text-xs text-gray-500 mt-2">Ngăn cách các từ khóa bằng dấu phẩy (,).</p>
        </div>
    </div>
    
    <!-- TAB 1.5: ROUTER -->
    <div id="tab-router" class="tab-content hidden">
        <div class="mb-8">
            <p class="text-sm text-gray-400 mb-6">Tùy chỉnh các tiền tố đường dẫn (Slug) trên URL. Hệ thống sẽ tự động cấu hình lại .htaccess cho bạn.</p>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug Phim (Mặc định: phim)</label>
                    <input type="text" name="slugMovie" value="<?= htmlspecialchars($settings['slugMovie'] ?? 'phim') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Vd: domain.com/<b>phim</b>/ten-phim</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug Xem Phim (Mặc định: xem-phim)</label>
                    <input type="text" name="slugWatch" value="<?= htmlspecialchars($settings['slugWatch'] ?? 'xem-phim') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Vd: domain.com/<b>xem-phim</b>/ten-phim/tap-1</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug Truyện (Mặc định: truyen)</label>
                    <input type="text" name="slugComic" value="<?= htmlspecialchars($settings['slugComic'] ?? 'truyen') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Vd: domain.com/<b>truyen</b>/ten-truyen</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug Đọc Truyện (Mặc định: doc-truyen)</label>
                    <input type="text" name="slugRead" value="<?= htmlspecialchars($settings['slugRead'] ?? 'doc-truyen') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Vd: domain.com/<b>doc-truyen</b>/ten-truyen/chap-1</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug Danh Sách (Mặc định: danh-sach)</label>
                    <input type="text" name="slugList" value="<?= htmlspecialchars($settings['slugList'] ?? 'danh-sach') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug Thể Loại (Mặc định: the-loai)</label>
                    <input type="text" name="slugGenre" value="<?= htmlspecialchars($settings['slugGenre'] ?? 'the-loai') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug Quốc Gia (Mặc định: quoc-gia)</label>
                    <input type="text" name="slugCountry" value="<?= htmlspecialchars($settings['slugCountry'] ?? 'quoc-gia') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>
    </div>
    
    <!-- TAB 2: XÁC MINH -->
    <div id="tab-verify" class="tab-content hidden">
        <div class="mb-8">
            <p class="text-sm text-gray-400 mb-6">Chỉ cần nhập ID xác minh (chuỗi mã), hệ thống sẽ tự động tạo thẻ meta tương ứng ngoài trang chủ.</p>
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Google Search Console</label>
                    <input type="text" name="verifyGoogle" value="<?= htmlspecialchars($settings['verifyGoogle'] ?? '') ?>" placeholder="Ví dụ: dX3...aF8" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Bing Webmaster</label>
                    <input type="text" name="verifyBing" value="<?= htmlspecialchars($settings['verifyBing'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Yandex Webmaster</label>
                    <input type="text" name="verifyYandex" value="<?= htmlspecialchars($settings['verifyYandex'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>
    </div>
    
    <!-- TAB 3: MÃ TÙY CHỈNH -->
    <div id="tab-code" class="tab-content hidden">
        <div class="mb-8">
            <p class="text-sm text-gray-400 mb-6">Cảnh báo: Việc chèn mã sai có thể làm hỏng giao diện trang web. Hãy cẩn thận!</p>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Chèn vào trước thẻ &lt;/head&gt;</label>
                    <p class="text-xs text-gray-500 mb-2">Phù hợp chèn mã Google Analytics, Facebook Pixel, CSS tùy chỉnh...</p>
                    <textarea name="customHead" rows="5" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none font-mono text-sm" placeholder="<script>...</script>"><?= htmlspecialchars($settings['customHead'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Chèn vào trước thẻ &lt;/body&gt;</label>
                    <p class="text-xs text-gray-500 mb-2">Phù hợp chèn mã Live Chat, mã theo dõi sự kiện...</p>
                    <textarea name="customBody" rows="5" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none font-mono text-sm" placeholder="<script>...</script>"><?= htmlspecialchars($settings['customBody'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors flex items-center shadow-lg shadow-blue-500/20">
        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình
    </button>
</form>

<script>
function switchTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.remove('block');
        el.classList.add('hidden');
    });
    // Remove active styles from buttons
    document.querySelectorAll('[id^="btn-tab-"]').forEach(el => {
        el.classList.remove('border-blue-500', 'text-blue-500');
        el.classList.add('border-transparent', 'text-gray-400');
    });
    
    // Show selected tab
    document.getElementById(tabId).classList.remove('hidden');
    document.getElementById(tabId).classList.add('block');
    // Set active style for selected button
    const btn = document.getElementById('btn-' + tabId);
    btn.classList.remove('border-transparent', 'text-gray-400');
    btn.classList.add('border-blue-500', 'text-blue-500');
}
</script>
