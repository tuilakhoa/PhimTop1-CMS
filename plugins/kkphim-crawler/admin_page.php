<div class="w-full max-w-full flex flex-col">
<div class="mb-6 flex justify-between items-center flex-wrap gap-4">
    <div>
        <h2 class="text-2xl font-bold text-white mb-2">Công Cụ Crawl Phim (Đa Nguồn)</h2>
        <p class="text-gray-400">Đồng bộ tự động dữ liệu phim, ảnh, danh sách, chi tiết, TMDB, diễn viên từ KKPhim, Nguồn C, VsMov.</p>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="flex border-b border-admin-border mb-6 gap-2 overflow-x-auto custom-scrollbar pb-2">
    <button class="tab-btn active px-4 py-2 text-admin-primary font-medium border-b-2 border-admin-primary whitespace-nowrap" data-tab="tab-auto">
        <i data-lucide="zap" class="w-4 h-4 inline-block mr-1"></i> Tự Động (Smart Sync)
    </button>
    <button class="tab-btn px-4 py-2 text-gray-400 font-medium hover:text-white border-b-2 border-transparent transition-colors whitespace-nowrap" data-tab="tab-manual">
        <i data-lucide="mouse-pointer-click" class="w-4 h-4 inline-block mr-1"></i> Crawl Thủ Công
    </button>
    <button class="tab-btn px-4 py-2 text-gray-400 font-medium hover:text-white border-b-2 border-transparent transition-colors whitespace-nowrap" data-tab="tab-mass">
        <i data-lucide="layers" class="w-4 h-4 inline-block mr-1"></i> Mass & Batch Crawl
    </button>
    <button class="tab-btn px-4 py-2 text-gray-400 font-medium hover:text-white border-b-2 border-transparent transition-colors whitespace-nowrap" data-tab="tab-failed">
        <i data-lucide="alert-triangle" class="w-4 h-4 inline-block mr-1"></i> Phim Lỗi
    </button>
</div>

<!-- Tab 1: Tự Động -->
<div id="tab-auto" class="tab-content grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Panel Kiểm Tra Cập Nhật Nhanh -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg  mb-6">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="bell-ring" class="text-green-400"></i> Theo Dõi Phim Mới (Trang 1)
        </h3>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-4">
            <div class="text-gray-300 text-sm flex-1">
                <p>Nhấn nút bên phải để hệ thống đối chiếu nhanh <strong>Trang 1</strong> của 3 nguồn với CSDL hiện tại.</p>
                <p class="mt-2 text-yellow-400 font-medium" id="checkUpdateResult">Trạng thái: Chưa kiểm tra.</p>
            </div>
                        <div class="flex items-center gap-2">
                <button id="btnCheckUpdate" class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-2.5 px-4 rounded-lg transition-colors flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="radar" class="w-4 h-4"></i> Kiểm Tra Trang 1
                </button>
                <button id="btnRunSmartSync" style="display: none;" class="bg-green-600 hover:bg-green-500 text-white font-medium py-2.5 px-5 rounded-lg transition-colors flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="zap" class="w-4 h-4"></i> Cập Nhật Nhanh (Smart Sync)
                </button>
            </div>
        </div>
        <p class="text-xs text-gray-500 italic">Tính năng này giúp bạn nắm bắt xem có phim nào vừa ra lò chưa được cập nhật không.</p>
    </div>
    <!-- Panel Cron Job -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="clock" class="text-admin-primary"></i> Thiết lập Cron Job Tự Động
        </h3>
        <div class="text-gray-300 text-sm space-y-2 mb-4">
            <p>Hệ thống tự động crawl phim mới nhất từ <code class="bg-black/50 px-1 py-0.5 rounded text-green-400">cả 3 nguồn (KKPhim, Nguồn C, VsMov)</code>.</p>
            <p>Hệ thống cũng tự nhận biết phim nào đã full hoặc có tập mới để tiến hành cập nhật lại.</p>
            <p>Sử dụng lệnh sau để chạy Cron qua CLI (Khuyên dùng):</p>
            <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-green-400">php <?= __DIR__ ?>/cron.php</code>
            <p class="mt-2">Hoặc thiết lập cron truy cập qua Web (nếu không có SSH):</p>
            <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-green-400"><?= (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . "/plugins/kkphim-crawler/cron.php?key=kkphim_cron" ?></code>
        </div>
    </div>
</div>

<!-- Tab 2: Thủ Công -->
<div id="tab-manual" class="tab-content hidden grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Panel Crawl Danh Sách -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="list-video" class="text-admin-primary"></i> Crawl Danh Sách Phim Mới
        </h3>
        
        <form id="crawlListForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Từ Trang</label>
                    <input type="number" name="from_page" value="1" min="1" class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white focus:outline-none focus:border-admin-primary transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Đến Trang</label>
                    <input type="number" name="to_page" value="1" min="1" class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white focus:outline-none focus:border-admin-primary transition-colors">
                </div>
            </div>
            <div class="flex items-center gap-2 mb-2">
                <input type="checkbox" id="fetch_images" name="fetch_images" class="rounded border-gray-600 bg-gray-700 text-admin-primary focus:ring-admin-primary">
                <label for="fetch_images" class="text-sm text-gray-300">Tải & lưu ảnh (Thumb/Poster) về server cục bộ</label>
            </div>
            
            <button type="submit" class="w-full bg-admin-primary hover:bg-admin-primary/90 text-white font-medium py-2.5 px-4 rounded-lg transition-colors flex justify-center items-center gap-2">
                <i data-lucide="play" class="w-4 h-4"></i> Bắt đầu Crawl
            </button>
        </form>
    </div>
    <!-- Panel Crawl Theo Từ Khóa -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="search" class="text-blue-400"></i> Crawl Phim Theo Từ Khóa
        </h3>
        
        <form id="crawlKeywordForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Từ Khóa</label>
                    <input type="text" name="keyword" placeholder="Nhập tên phim..." class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-400 transition-colors" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Số Lượng</label>
                    <input type="number" name="limit" value="10" min="1" max="100" class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-400 transition-colors">
                </div>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors flex justify-center items-center gap-2">
                <i data-lucide="play" class="w-4 h-4"></i> Tìm & Crawl
            </button>
        </form>
    </div>
    <!-- Panel Crawl 1 Phim -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="file-search" class="text-admin-primary"></i> Crawl Chi Tiết 1 Phim
        </h3>
        
        <form id="crawlSingleForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Slug của phim</label>
                <input type="text" name="movie_slug" placeholder="vd: lightyear-canh-sat-vu-tru" required class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white focus:outline-none focus:border-admin-primary transition-colors">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors flex justify-center items-center gap-2">
                <i data-lucide="download-cloud" class="w-4 h-4"></i> Crawl Phim Này
            </button>
        </form>
    </div>
</div>
</div>

<!-- Tab 3: Mass & Batch -->
<div id="tab-mass" class="tab-content hidden grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Panel Mass Crawl -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="rocket" class="text-red-400"></i> Crawl Hàng Loạt Tốc Độ Cao
        </h3>
        <div class="text-gray-300 text-sm space-y-2 mb-4">
            <p>Dành riêng cho việc crawl toàn bộ 30.000 phim mới hoàn toàn. Công cụ sử dụng đa luồng (Multi-cURL) giúp tăng tốc độ lên gấp 20 lần.</p>
            <p>Vì số lượng quá lớn, bạn <strong>bắt buộc</strong> phải chạy qua <strong>Terminal (CLI)</strong> trên VPS/Server để tránh lỗi timeout của Web.</p>
            <p>Cú pháp chạy: <code class="bg-black/50 px-1 py-0.5 rounded text-red-400">php mass_crawl.php [từ_trang] [đến_trang]</code></p>
            <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-red-400">php <?= __DIR__ ?>/mass_crawl.php 1 1500</code>
        </div>
    </div>
    <!-- Panel Cron Batch (Crawl 20 trang mỗi lần) -->
    <?php
    $cronBatchFile = __DIR__ . '/cron_batch_progress.txt';
    $cronBatchPage = file_exists($cronBatchFile) ? (int)trim(file_get_contents($cronBatchFile)) : 1;
    ?>
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="list-ordered" class="text-purple-400"></i> Cron Crawl Luân Phiên (20 Trang)
        </h3>
        <div class="text-gray-300 text-sm space-y-2 mb-4">
            <p>Cron job đặc biệt giúp crawl dần dữ liệu phim cũ, <strong>mỗi lần chạy sẽ tự động crawl 20 trang</strong> và ghi nhớ tiến độ.</p>
            <div class="bg-black/40 p-3 rounded-lg border border-purple-500/30 flex flex-col sm:flex-row justify-between items-center my-3 gap-3">
                <span>Trang hiện hành (sẽ crawl tiếp theo):</span>
                <div class="flex items-center gap-2">
                    <input type="number" id="inputCronBatchProgress" value="<?= $cronBatchPage ?>" min="1" class="w-24 bg-black/50 border border-admin-border rounded-lg px-2 py-1 text-white text-center font-bold text-lg focus:outline-none focus:border-purple-500 transition-colors">
                    <button id="btnSetCronBatch" class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-1.5 rounded-lg text-sm transition-colors">Lưu lại</button>
                </div>
            </div>
            <p>Cú pháp chạy CLI (cron):</p>
            <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-purple-400">php <?= __DIR__ ?>/cron_batch.php</code>
            <p class="mt-2">Hoặc chạy qua Web Cron (Cpanel/DirectAdmin):</p>
            <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-purple-400"><?= (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . "/plugins/kkphim-crawler/cron_batch.php?key=kkphim_cron" ?></code>
        </div>
        <div class="flex gap-2">
            <button id="btnResetCronBatch" class="bg-red-600/80 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-sm">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset về Trang 1
            </button>
        </div>
    </div>
</div>

<!-- Tab 4: Failed -->
<div id="tab-failed" class="tab-content hidden grid grid-cols-1 gap-6">
    <!-- Panel Phim Lỗi (Failed Movies) -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg ">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="alert-triangle" class="text-yellow-400"></i> Quản Lý Phim Lỗi Mạng
        </h3>
        <div class="flex items-center justify-between mb-4">
            <div class="text-gray-300 text-sm">
                Số phim crawl lỗi cần tải lại: <strong id="failedCount" class="text-yellow-400 text-lg">0</strong> phim
            </div>
            <button id="btnRecrawlFailed" class="bg-yellow-600 hover:bg-yellow-500 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Crawl Lại Phim Lỗi
            </button>
        </div>
        <p class="text-xs text-gray-400 italic">Tính năng này giúp bạn thử tải lại những phim bị lỗi từ quá trình "Crawl Hàng Loạt". Khi tải thành công, số lượng sẽ tự động giảm.</p>
    </div>
</div>

<!-- Output Log Area -->
<div class="mt-6 bg-black/80 rounded-xl border border-admin-border overflow-hidden flex flex-col" style="height: 450px;">
    <div class="flex items-center justify-between px-4 py-3 border-b border-admin-border bg-admin-panel">
        <h3 class="text-sm font-bold text-gray-300 flex items-center gap-2">
            <i data-lucide="terminal" class="w-4 h-4 text-green-400"></i> Tiến Trình Crawl
        </h3>
        <button id="clearLogBtn" class="text-xs text-gray-500 hover:text-white transition-colors bg-gray-800 px-2 py-1 rounded">Xóa Log</button>
    </div>
    <div id="crawlLog" class="p-4 flex-1 font-mono text-sm space-y-1 text-gray-300 custom-scrollbar" style="overflow-y: auto; max-height: calc(450px - 50px);">
        <div class="text-gray-500 italic">Sẵn sàng...</div>
    </div>
</div>

<style>
/* Tùy chỉnh thanh cuộn cho đẹp mắt */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.3);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.4);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tabs Logic
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active classes
            tabBtns.forEach(b => {
                b.classList.remove('active', 'text-admin-primary', 'border-admin-primary');
                b.classList.add('text-gray-400', 'border-transparent');
            });
            tabContents.forEach(c => c.classList.add('hidden'));
            
            // Add active class
            btn.classList.add('active', 'text-admin-primary', 'border-admin-primary');
            btn.classList.remove('text-gray-400', 'border-transparent');
            
            // Show content
            const targetId = btn.getAttribute('data-tab');
            const targetEl = document.getElementById(targetId);
            if(targetEl) targetEl.classList.remove('hidden');
        });
    });
    if (typeof lucide !== 'undefined') lucide.createIcons();


    const logEl = document.getElementById('crawlLog');
    const MAX_LOG_LINES = 200; // Giới hạn số dòng để tránh đơ trình duyệt
    
    function logMessage(msg, type = 'info') {
        const div = document.createElement('div');
        const time = new Date().toLocaleTimeString();
        let colorClass = 'text-gray-300';
        if (type === 'success') colorClass = 'text-green-400';
        else if (type === 'error') colorClass = 'text-red-400';
        else if (type === 'warn') colorClass = 'text-yellow-400';
        
        div.className = colorClass;
        div.innerHTML = `<span class="text-gray-600">[${time}]</span> ${msg}`;
        logEl.appendChild(div);
        
        // Xóa bớt log cũ nếu vượt quá giới hạn
        while (logEl.children.length > MAX_LOG_LINES) {
            logEl.removeChild(logEl.firstChild);
        }
        
        // Tự động cuộn xuống dưới cùng
        logEl.scrollTop = logEl.scrollHeight;
    }

    document.getElementById('clearLogBtn').addEventListener('click', () => {
        logEl.innerHTML = '';
    });

    const pluginPath = '/plugins/kkphim-crawler/ajax.php';
    let failedSlugsList = [];

    // Tải danh sách phim lỗi
    async function loadFailedSlugs() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_failed_slugs');
            const res = await fetch(pluginPath, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                failedSlugsList = data.slugs;
                document.getElementById('failedCount').textContent = failedSlugsList.length;
                document.getElementById('btnRecrawlFailed').disabled = failedSlugsList.length === 0;
            }
        } catch (e) {
            console.error('Cannot load failed slugs', e);
        }
    }
    
    // Gọi khi load trang
    loadFailedSlugs();

    // Xử lý Recrawl phim lỗi
    document.getElementById('btnRecrawlFailed').addEventListener('click', async () => {
        if (failedSlugsList.length === 0) return;
        
        const btn = document.getElementById('btnRecrawlFailed');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang tải lại...';
        if (typeof lucide !== 'undefined') lucide.createIcons();


        logMessage(`Bắt đầu thử tải lại ${failedSlugsList.length} phim lỗi...`, 'warn');
        
        let successCount = 0;
        let failCount = 0;
        
        // Copy mảng để loop
        const slugsToProcess = [...failedSlugsList];

        for (let i = 0; i < slugsToProcess.length; i++) {
            const slug = slugsToProcess[i];
            const mData = new FormData();
            mData.append('action', 'crawl_single');
            mData.append('slug', slug);
            mData.append('fetch_images', document.getElementById('fetch_images')?.checked ? '1' : '0'); // Nếu có
            
            try {
                const mRes = await fetch(pluginPath, { method: 'POST', body: mData });
                const mJson = await mRes.json();
                
                if (mJson.status === 'success') {
                    logMessage(`[Phim Lỗi - ${i+1}/${slugsToProcess.length}] Tải thành công: <b>${mJson.movie_name}</b>`, 'success');
                    successCount++;
                    
                    // Xóa khỏi file log lỗi
                    const rData = new FormData();
                    rData.append('action', 'remove_failed_slug');
                    rData.append('slug', slug);
                    await fetch(pluginPath, { method: 'POST', body: rData });
                    
                    // Cập nhật mảng và UI
                    failedSlugsList = failedSlugsList.filter(s => s !== slug);
                    document.getElementById('failedCount').textContent = failedSlugsList.length;
                } else {
                    logMessage(`[Phim Lỗi - ${i+1}/${slugsToProcess.length}] Vẫn lỗi (${slug}): ${mJson.message}`, 'error');
                    failCount++;
                }
            } catch (e) {
                logMessage(`[Phim Lỗi - ${i+1}/${slugsToProcess.length}] Lỗi mạng (${slug})`, 'error');
                failCount++;
            }
        }
        
        logMessage(`<b>Hoàn thành tải lại! Thành công: ${successCount}, Lỗi: ${failCount}</b>`, 'info');
        
        btn.innerHTML = '<i data-lucide="refresh-cw" class="w-4 h-4"></i> Crawl Lại Phim Lỗi';
        btn.disabled = failedSlugsList.length === 0;
        if (typeof lucide !== 'undefined') lucide.createIcons();

    });

    // Crawl Single Movie
    document.getElementById('crawlSingleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const slug = e.target.movie_slug.value.trim();
        if (!slug) return;
        
        logMessage(`Đang lấy dữ liệu cho slug: <b>${slug}</b>...`, 'info');
        
        try {
            const formData = new FormData();
            formData.append('action', 'crawl_single');
            formData.append('slug', slug);
            formData.append('fetch_images', document.getElementById('fetch_images').checked ? '1' : '0');

            const res = await fetch(pluginPath, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                logMessage(`Thành công: Đã crawl phim <b>${data.movie_name}</b>`, 'success');
            } else {
                logMessage(`Lỗi: ${data.message}`, 'error');
            }
        } catch (error) {
            logMessage(`Đã xảy ra lỗi mạng: ${error.message}`, 'error');
        }
    });

    // Reset Cron Batch Progress
    const btnResetCronBatch = document.getElementById('btnResetCronBatch');
    const inputCronBatchProgress = document.getElementById('inputCronBatchProgress');
    if (btnResetCronBatch && inputCronBatchProgress) {
        btnResetCronBatch.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc chắn muốn reset tiến trình Cron Batch về lại trang 1?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'reset_cron_batch');
                const res = await fetch(pluginPath, { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.status === 'success') {
                    inputCronBatchProgress.value = '1';
                    logMessage('Đã reset tiến độ Cron Batch về trang 1', 'success');
                } else {
                    logMessage('Lỗi khi reset: ' + data.message, 'error');
                }
            } catch (e) {
                logMessage('Lỗi mạng khi reset', 'error');
            }
        });
    }

    // Tùy chỉnh (Set) Cron Batch Progress
    const btnSetCronBatch = document.getElementById('btnSetCronBatch');
    if (btnSetCronBatch && inputCronBatchProgress) {
        btnSetCronBatch.addEventListener('click', async () => {
            const newPage = parseInt(inputCronBatchProgress.value);
            if (isNaN(newPage) || newPage < 1) {
                logMessage('Vui lòng nhập số trang hợp lệ!', 'error');
                return;
            }
            
            btnSetCronBatch.disabled = true;
            btnSetCronBatch.textContent = 'Đang lưu...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'set_cron_batch_progress');
                formData.append('page', newPage);
                const res = await fetch(pluginPath, { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.status === 'success') {
                    logMessage(data.message, 'success');
                } else {
                    logMessage('Lỗi cập nhật: ' + data.message, 'error');
                }
            } catch (e) {
                logMessage('Lỗi kết nối khi cập nhật tiến độ', 'error');
            }
            
            btnSetCronBatch.disabled = false;
            btnSetCronBatch.textContent = 'Lưu lại';
        });
    }

        // Check update
    const btnCheckUpdate = document.getElementById('btnCheckUpdate');
    const checkUpdateResult = document.getElementById('checkUpdateResult');
    if (btnCheckUpdate && checkUpdateResult) {
        btnCheckUpdate.addEventListener('click', async () => {
            btnCheckUpdate.disabled = true;
            btnCheckUpdate.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang quét...';
            if (typeof lucide !== 'undefined') lucide.createIcons();

            checkUpdateResult.textContent = 'Trạng thái: Đang lấy dữ liệu từ các nguồn...';
            checkUpdateResult.className = "mt-2 text-yellow-400 font-medium";

            try {
                const formData = new FormData();
                formData.append('action', 'check_new_movies');
                const res = await fetch(pluginPath, { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.status === 'success') {
                    checkUpdateResult.textContent = 'Trạng thái: ' + data.message;
                    if (data.total > 0) {
                        checkUpdateResult.className = "mt-2 text-green-400 font-bold";
                        if(document.getElementById("btnRunSmartSync")) document.getElementById("btnRunSmartSync").style.display = "flex";
                    } else {
                        checkUpdateResult.className = "mt-2 text-gray-400 font-medium";
                    }
                } else {
                    checkUpdateResult.textContent = 'Lỗi: ' + data.message;
                    checkUpdateResult.className = "mt-2 text-red-400 font-medium";
                }
            } catch (e) {
                checkUpdateResult.textContent = 'Lỗi mạng khi gọi API.';
                checkUpdateResult.className = "mt-2 text-red-400 font-medium";
            }
            
            btnCheckUpdate.disabled = false;
            btnCheckUpdate.innerHTML = '<i data-lucide="radar" class="w-4 h-4"></i> Kiểm Tra Ngay';
            if (typeof lucide !== 'undefined') lucide.createIcons();

        });
    }
        // Smart Sync
    const btnRunSmartSync = document.getElementById('btnRunSmartSync');
    if (btnRunSmartSync) {
        btnRunSmartSync.addEventListener('click', async () => {
            btnRunSmartSync.disabled = true;
            btnRunSmartSync.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang đồng bộ...';
            if (typeof lucide !== 'undefined') lucide.createIcons();

            checkUpdateResult.textContent = 'Trạng thái: Đang chạy Smart Sync...';
            checkUpdateResult.className = "mt-2 text-green-400 font-bold";
            
            const sources = ['kkphim', 'nguonc', 'vsmov'];
            let totalUpdated = 0;
            
            for (let source of sources) {
                logMessage(`<b>=== BẮT ĐẦU NGUỒN: ${source.toUpperCase()} ===</b>`, 'info');
                let shouldStop = false;
                let consecutive = 0;
                
                for (let page = 1; page <= 10; page++) {
                    logMessage(`Đang xử lý Trang ${page} của ${source.toUpperCase()}...`, 'warn');
                    try {
                        const formData = new FormData();
                        formData.append('action', 'smart_sync_source');
                        formData.append('source', source);
                        formData.append('page', page);
                        formData.append('consecutive', consecutive);
                        
                        const res = await fetch(pluginPath, { method: 'POST', body: formData });
                        const data = await res.json();
                        
                        if (data.status === 'success') {
                            if (data.logs && data.logs.length > 0) {
                                data.logs.forEach(log => {
                                    let type = log.includes('Phát hiện') ? 'success' : (log.includes('Cập nhật') ? 'warn' : 'info');
                                    logMessage(`[${source}] ${log}`, type);
                                });
                            }
                            totalUpdated += data.updated;
                            consecutive = data.consecutive;
                            
                            if (data.should_stop) {
                                shouldStop = true;
                                break;
                            }
                        } else {
                            logMessage(`[${source}] Lỗi API nội bộ.`, 'error');
                            break;
                        }
                    } catch (e) {
                        logMessage(`[${source}] Lỗi mạng khi gọi trang ${page}.`, 'error');
                        break;
                    }
                }
                if (!shouldStop) {
                    logMessage(`Đã quét tối đa 10 trang của ${source.toUpperCase()}. Chuyển nguồn...`, 'info');
                }
            }
            
            logMessage(`<b>HOÀN THÀNH SMART SYNC! TỔNG CỘNG CẬP NHẬT: ${totalUpdated} PHIM.</b>`, 'success');
            checkUpdateResult.textContent = `Hoàn thành! Đã cập nhật ${totalUpdated} phim.`;
            checkUpdateResult.className = "mt-2 text-green-400 font-bold";
            
            btnRunSmartSync.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Hoàn Thành';
            if (typeof lucide !== 'undefined') lucide.createIcons();

            
            setTimeout(() => {
                btnRunSmartSync.disabled = false;
                btnRunSmartSync.innerHTML = '<i data-lucide="zap" class="w-4 h-4"></i> Cập Nhật Nhanh (Smart Sync)';
                btnRunSmartSync.style.display = 'none';
                if (typeof lucide !== 'undefined') lucide.createIcons();

            }, 3000);
        });
    }

    // Crawl List
    document.getElementById('crawlListForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fromPage = parseInt(e.target.from_page.value);
        const toPage = parseInt(e.target.to_page.value);
        const fetchImages = document.getElementById('fetch_images').checked ? '1' : '0';
        
        if (fromPage > toPage) {
            logMessage("Trang bắt đầu phải nhỏ hơn hoặc bằng trang kết thúc!", "error");
            return;
        }

        const btn = e.target.querySelector('button');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang Crawl...';
        if (typeof lucide !== 'undefined') lucide.createIcons();


        logMessage(`Bắt đầu crawl từ trang ${fromPage} đến ${toPage}...`, 'info');

        for (let page = fromPage; page <= toPage; page++) {
            logMessage(`Đang lấy danh sách phim trang ${page}...`, 'warn');
            try {
                // 1. Get slugs for this page
                const pData = new FormData();
                pData.append('action', 'get_page_slugs');
                pData.append('page', page);
                
                const pRes = await fetch(pluginPath, { method: 'POST', body: pData });
                const pJson = await pRes.json();
                
                if (pJson.status !== 'success') {
                    logMessage(`Lỗi trang ${page}: ${pJson.message}`, 'error');
                    continue;
                }

                const slugs = pJson.slugs || [];
                logMessage(`Trang ${page} có ${slugs.length} phim. Bắt đầu fetch từng phim...`, 'info');

                // 2. Fetch each movie in sequence
                for (let i = 0; i < slugs.length; i++) {
                    const slug = slugs[i];
                    const mData = new FormData();
                    mData.append('action', 'crawl_single');
                    mData.append('slug', slug);
                    mData.append('fetch_images', fetchImages);
                    
                    try {
                        const mRes = await fetch(pluginPath, { method: 'POST', body: mData });
                        const mJson = await mRes.json();
                        
                        if (mJson.status === 'success') {
                            logMessage(`[Trang ${page} - ${i+1}/${slugs.length}] Đã lưu: <b>${mJson.movie_name}</b>`, 'success');
                        } else {
                            logMessage(`[Trang ${page} - ${i+1}/${slugs.length}] Lỗi (${slug}): ${mJson.message}`, 'error');
                        }
                    } catch (e) {
                        logMessage(`[Trang ${page} - ${i+1}/${slugs.length}] Lỗi mạng (${slug})`, 'error');
                    }
                }
            } catch (err) {
                logMessage(`Lỗi kết nối khi crawl trang ${page}: ${err.message}`, 'error');
            }
        }
        
        logMessage('<b>Hoàn thành tiến trình crawl danh sách!</b>', 'success');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i> Bắt đầu Crawl';
        if (typeof lucide !== 'undefined') lucide.createIcons();

    });

    // Crawl Keyword
    const crawlKeywordForm = document.getElementById('crawlKeywordForm');
    if (crawlKeywordForm) {
        crawlKeywordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const keyword = e.target.keyword.value.trim();
            const limit = parseInt(e.target.limit.value) || 10;
            const fetchImages = document.getElementById('fetch_images')?.checked ? '1' : '0';
            
            if (!keyword) return;

            const btn = e.target.querySelector('button');
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang Tìm...';
            if (typeof lucide !== 'undefined') lucide.createIcons();


            logMessage(`Đang tìm kiếm phim với từ khóa: <b>${keyword}</b>...`, 'info');

            try {
                // 1. Get slugs for this keyword
                const pData = new FormData();
                pData.append('action', 'crawl_keyword');
                pData.append('keyword', keyword);
                pData.append('limit', limit);
                
                const pRes = await fetch(pluginPath, { method: 'POST', body: pData });
                const pJson = await pRes.json();
                
                if (pJson.status !== 'success') {
                    logMessage(`Lỗi tìm kiếm: ${pJson.message}`, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i> Tìm & Crawl';
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    return;
                }

                const slugs = pJson.slugs || [];
                logMessage(`Tìm thấy ${slugs.length} phim. Bắt đầu tải...`, 'info');

                // 2. Fetch each movie in sequence
                for (let i = 0; i < slugs.length; i++) {
                    const slug = slugs[i];
                    const mData = new FormData();
                    mData.append('action', 'crawl_single');
                    mData.append('slug', slug);
                    mData.append('fetch_images', fetchImages);
                    
                    try {
                        const mRes = await fetch(pluginPath, { method: 'POST', body: mData });
                        const mJson = await mRes.json();
                        
                        if (mJson.status === 'success') {
                            logMessage(`[Từ khóa - ${i+1}/${slugs.length}] Đã lưu: <b>${mJson.movie_name}</b>`, 'success');
                        } else {
                            logMessage(`[Từ khóa - ${i+1}/${slugs.length}] Lỗi (${slug}): ${mJson.message}`, 'error');
                        }
                    } catch (e) {
                        logMessage(`[Từ khóa - ${i+1}/${slugs.length}] Lỗi mạng (${slug})`, 'error');
                    }
                }
                logMessage('<b>Hoàn thành tải phim theo từ khóa!</b>', 'success');
            } catch (err) {
                logMessage(`Lỗi kết nối: ${err.message}`, 'error');
            }
            
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i> Tìm & Crawl';
            if (typeof lucide !== 'undefined') lucide.createIcons();

        });
    }
});
</script>

</div>