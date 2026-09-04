<div class="mb-6">
    <h2 class="text-2xl font-bold text-white mb-2">Công Cụ Crawl Phim (KKPhim)</h2>
    <p class="text-gray-400">Đồng bộ tự động dữ liệu phim, ảnh, danh sách, chi tiết, TMDB, diễn viên từ KKPhim (phimapi.com) bản mới nhất.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

    <!-- Panel Cron Job -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="clock" class="text-admin-primary"></i> Thiết lập Cron Job Tự Động
        </h3>
        <div class="text-gray-300 text-sm space-y-2 mb-4">
            <p>Hệ thống tự động crawl phim mới nhất từ <code class="bg-black/50 px-1 py-0.5 rounded text-green-400">https://phimapi.com/v1/api/home</code>.</p>
            <p>Hệ thống cũng tự nhận biết phim nào đã full hoặc có tập mới để tiến hành cập nhật lại.</p>
            <p>Sử dụng lệnh sau để chạy Cron qua CLI (Khuyên dùng):</p>
            <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-green-400">php <?= __DIR__ ?>/cron.php</code>
            <p class="mt-2">Hoặc thiết lập cron truy cập qua Web (nếu không có SSH):</p>
            <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-green-400"><?= (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . "/plugins/kkphim-crawler/cron.php?key=kkphim_cron" ?></code>
        </div>
    </div>

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

    <!-- Panel Phim Lỗi (Failed Movies) -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg lg:col-span-2">
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
});
</script>
