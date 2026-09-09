<div class="w-full max-w-full flex flex-col">
<div class="mb-6 flex justify-between items-center flex-wrap gap-4">
    <div>
        <h2 class="text-2xl font-bold text-white mb-2">Công Cụ Crawl Phim (Đa Nguồn)</h2>
        <p class="text-gray-400">Đồng bộ tự động dữ liệu phim, ảnh, danh sách, chi tiết, TMDB, diễn viên từ KKPhim, Nguồn C, VsMov.</p>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="flex border-b border-admin-border mb-6 gap-2 overflow-x-auto custom-scrollbar pb-2">
    <button class="tab-btn active px-4 py-2 text-admin-primary font-medium border-b-2 border-admin-primary whitespace-nowrap" data-tab="tab-kkphim">
        <i data-lucide="server" class="w-4 h-4 inline-block mr-1"></i> KKPhim
    </button>
    <button class="tab-btn px-4 py-2 text-gray-400 font-medium hover:text-white border-b-2 border-transparent transition-colors whitespace-nowrap" data-tab="tab-nguonc">
        <i data-lucide="server" class="w-4 h-4 inline-block mr-1"></i> Nguồn C
    </button>
    <button class="tab-btn px-4 py-2 text-gray-400 font-medium hover:text-white border-b-2 border-transparent transition-colors whitespace-nowrap" data-tab="tab-vsmov">
        <i data-lucide="server" class="w-4 h-4 inline-block mr-1"></i> VsMov
    </button>
    <button class="tab-btn px-4 py-2 text-gray-400 font-medium hover:text-white border-b-2 border-transparent transition-colors whitespace-nowrap" data-tab="tab-config">
        <i data-lucide="settings" class="w-4 h-4 inline-block mr-1"></i> Cấu Hình & Cron
    </button>
</div>

<?php 
$sources = [
    'kkphim' => 'KKPhim',
    'nguonc' => 'Nguồn C',
    'vsmov'  => 'VsMov'
];

foreach ($sources as $sourceKey => $sourceName): 
?>
<!-- Tab <?= $sourceName ?> -->
<div id="tab-<?= $sourceKey ?>" class="tab-content <?= $sourceKey === 'kkphim' ? '' : 'hidden' ?> grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Panel Smart Sync -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="zap" class="text-green-400"></i> Smart Sync (<?= $sourceName ?>)
        </h3>
        <p class="text-gray-300 text-sm mb-4">Tự động phát hiện các phim mới/cập nhật trên <?= $sourceName ?> và đồng bộ về hệ thống.</p>
        <p class="mb-4 text-yellow-400 font-medium text-sm" id="checkResult_<?= $sourceKey ?>">Trạng thái: Chưa kiểm tra.</p>
        
        <div class="flex gap-2">
            <button class="btn-check-update bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg flex items-center gap-2" data-source="<?= $sourceKey ?>">
                <i data-lucide="radar" class="w-4 h-4"></i> Kiểm Tra
            </button>
            <button class="btn-run-smart-sync bg-green-600 hover:bg-green-500 text-white font-medium py-2 px-4 rounded-lg flex items-center gap-2 hidden" data-source="<?= $sourceKey ?>">
                <i data-lucide="play" class="w-4 h-4"></i> Bắt đầu Sync
            </button>
        </div>
    </div>

    <!-- Panel Crawl Danh Sách -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="list-video" class="text-admin-primary"></i> Crawl Theo Trang (<?= $sourceName ?>)
        </h3>
        <form class="crawl-list-form space-y-4" data-source="<?= $sourceKey ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Từ Trang</label>
                    <input type="number" name="from_page" value="1" min="1" class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Đến Trang</label>
                    <input type="number" name="to_page" value="1" min="1" class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white">
                </div>
            </div>
            <div class="flex items-center gap-2 mb-2">
                <input type="checkbox" name="fetch_images" class="rounded border-gray-600 bg-gray-700 text-admin-primary focus:ring-admin-primary">
                <label class="text-sm text-gray-300">Tải & lưu ảnh (Thumb/Poster)</label>
            </div>
            <button type="submit" class="w-full bg-admin-primary hover:bg-admin-primary/90 text-white font-medium py-2.5 px-4 rounded-lg flex justify-center items-center gap-2">
                <i data-lucide="play" class="w-4 h-4"></i> Bắt đầu Crawl
            </button>
        </form>
    </div>

    <!-- Panel Crawl Từ Khóa -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg lg:col-span-2">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="search" class="text-admin-primary"></i> Tìm & Crawl (<?= $sourceName ?>)
        </h3>
        <form class="crawl-keyword-form flex gap-4 items-end" data-source="<?= $sourceKey ?>">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-400 mb-1">Từ khóa (Tên phim...)</label>
                <input type="text" name="keyword" class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white" placeholder="Ví dụ: One Piece">
            </div>
            <div class="w-24">
                <label class="block text-sm font-medium text-gray-400 mb-1">Giới hạn</label>
                <input type="number" name="limit" value="10" min="1" max="100" class="w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-white">
            </div>
            <button type="submit" class="bg-admin-primary hover:bg-admin-primary/90 text-white font-medium py-2.5 px-4 rounded-lg flex items-center gap-2 h-[42px]">
                <i data-lucide="search" class="w-4 h-4"></i> Tìm & Crawl
            </button>
        </form>
    </div>
</div>
<?php endforeach; ?>

<!-- Tab Cấu Hình & Cron -->
<div id="tab-config" class="tab-content hidden grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Phim Lỗi -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i data-lucide="alert-triangle" class="text-red-400"></i> Phim Lỗi (<span id="failedCount">0</span>)
            </h3>
            <button id="btnRecrawlFailed" disabled class="bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white text-sm font-medium py-1.5 px-3 rounded-lg flex items-center gap-1 transition-colors">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Thử Crawl Lại
            </button>
        </div>
        <p class="text-gray-400 text-sm mb-4">Danh sách các slug phim bị lỗi trong quá trình crawl tự động. Bấm thử crawl lại để hệ thống lấy lại dữ liệu.</p>
    </div>

    <!-- Cron Job -->
    <div class="bg-admin-panel rounded-xl border border-admin-border p-6 shadow-lg">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="clock" class="text-admin-primary"></i> Hướng Dẫn Cài Đặt Cron
        </h3>
        <div class="text-gray-300 text-sm space-y-3">
            <p>Hệ thống tự động đồng bộ tập mới và phim mới bằng Smart Sync.</p>
            <div>
                <p class="font-bold text-white mb-1">1. Chạy tất cả các nguồn:</p>
                <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-green-400 break-all">php <?= __DIR__ ?>/cron.php</code>
            </div>
            <div>
                <p class="font-bold text-white mb-1">2. Chạy từng nguồn riêng (ví dụ KKPhim):</p>
                <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-green-400 break-all">php <?= __DIR__ ?>/cron.php kkphim</code>
            </div>
            <div>
                <p class="font-bold text-white mb-1">3. Cron qua URL (nếu không có SSH):</p>
                <code class="block w-full bg-black/50 border border-admin-border rounded-lg px-4 py-2 text-green-400 break-all"><?= (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . "/plugins/kkphim-crawler/cron.php?key=kkphim_cron&source=kkphim" ?></code>
            </div>
        </div>
    </div>
</div>

<!-- Logger Bắt Buộc Có -->
<div class="bg-black/90 rounded-xl border border-admin-border p-4 shadow-lg flex flex-col h-[400px]">
    <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-800">
        <h3 class="text-sm font-bold text-gray-400 flex items-center gap-2">
            <i data-lucide="terminal" class="w-4 h-4"></i> Tiến Trình Crawl
        </h3>
        <button onclick="document.getElementById('crawlLog').innerHTML=''" class="text-xs text-gray-500 hover:text-white transition-colors">Xóa Log</button>
    </div>
    <div id="crawlLog" class="flex-1 overflow-y-auto custom-scrollbar font-mono text-sm space-y-1 p-2 bg-black rounded">
        <div class="text-gray-500 italic">Sẵn sàng...</div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tabs Logic
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.classList.remove('active', 'text-admin-primary', 'border-admin-primary');
                b.classList.add('text-gray-400', 'border-transparent');
            });
            tabContents.forEach(c => c.classList.add('hidden'));
            
            btn.classList.add('active', 'text-admin-primary', 'border-admin-primary');
            btn.classList.remove('text-gray-400', 'border-transparent');
            
            const targetId = btn.getAttribute('data-tab');
            const targetEl = document.getElementById(targetId);
            if(targetEl) targetEl.classList.remove('hidden');
        });
    });
    if (typeof lucide !== 'undefined') lucide.createIcons();

    const logEl = document.getElementById('crawlLog');
    const pluginPath = '/plugins/kkphim-crawler/ajax.php';
    let failedSlugsList = [];

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
        logEl.scrollTop = logEl.scrollHeight;
    }

    async function loadFailedSlugs() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_failed_slugs');
            const res = await fetch(pluginPath, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                failedSlugsList = data.slugs || [];
                document.getElementById('failedCount').textContent = failedSlugsList.length;
                document.getElementById('btnRecrawlFailed').disabled = failedSlugsList.length === 0;
            }
        } catch (e) { console.error(e); }
    }
    loadFailedSlugs();

    // Check Update
    document.querySelectorAll('.btn-check-update').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const source = btn.getAttribute('data-source');
            const resLabel = document.getElementById('checkResult_' + source);
            const syncBtn = btn.parentElement.querySelector('.btn-run-smart-sync');
            
            btn.disabled = true;
            resLabel.className = 'mb-4 text-yellow-400 font-medium text-sm';
            resLabel.textContent = 'Trạng thái: Đang kiểm tra...';
            
            try {
                const fd = new FormData();
                fd.append('action', 'check_new_movies');
                fd.append('source', source);
                
                const res = await fetch(pluginPath, { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.status === 'success') {
                    resLabel.textContent = `Trạng thái: ${data.message}`;
                    if (data.total > 0) {
                        resLabel.className = 'mb-4 text-green-400 font-medium text-sm';
                        syncBtn.classList.remove('hidden');
                    } else {
                        resLabel.className = 'mb-4 text-gray-400 font-medium text-sm';
                        syncBtn.classList.add('hidden');
                    }
                } else {
                    resLabel.textContent = `Trạng thái: Lỗi: ${data.message}`;
                    resLabel.className = 'mb-4 text-red-400 font-medium text-sm';
                }
            } catch (err) {
                resLabel.textContent = `Trạng thái: Lỗi kết nối: ${err.message}`;
                resLabel.className = 'mb-4 text-red-400 font-medium text-sm';
            }
            btn.disabled = false;
        });
    });

    // Run Smart Sync
    document.querySelectorAll('.btn-run-smart-sync').forEach(btn => {
        btn.addEventListener('click', async () => {
            const source = btn.getAttribute('data-source');
            const resLabel = document.getElementById('checkResult_' + source);
            
            btn.disabled = true;
            btn.classList.add('opacity-50');
            resLabel.textContent = 'Trạng thái: Đang Sync...';
            logMessage(`Bắt đầu chạy Smart Sync cho nguồn ${source}...`, 'info');
            
            let page = 1;
            let consecutive = 0;
            let shouldStop = false;
            
            while (!shouldStop && page <= 50) {
                logMessage(`[${source}] Đang Sync trang ${page}...`, 'warn');
                try {
                    const fd = new FormData();
                    fd.append('action', 'smart_sync_page');
                    fd.append('source', source);
                    fd.append('page', page);
                    fd.append('consecutive', consecutive);
                    
                    const res = await fetch(pluginPath, { method: 'POST', body: fd });
                    const data = await res.json();
                    
                    if (data.status === 'success') {
                        if (data.logs) data.logs.forEach(l => logMessage(l, 'info'));
                        consecutive = data.consecutive || 0;
                        shouldStop = data.should_stop || false;
                        if (shouldStop) {
                            logMessage(`Hoàn tất Smart Sync ${source}!`, 'success');
                            break;
                        }
                    } else {
                        logMessage(`Lỗi Sync: ${data.message}`, 'error');
                        break;
                    }
                } catch (e) {
                    logMessage(`Lỗi mạng Sync: ${e.message}`, 'error');
                    break;
                }
                page++;
            }
            
            resLabel.textContent = 'Trạng thái: Đã hoàn tất Smart Sync.';
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            btn.classList.add('hidden');
        });
    });

    // Crawl List
    document.querySelectorAll('.crawl-list-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const source = form.getAttribute('data-source');
            const fromPage = parseInt(form.from_page.value);
            const toPage = parseInt(form.to_page.value);
            const fetchImages = form.fetch_images.checked ? '1' : '0';
            const btn = form.querySelector('button[type="submit"]');
            
            if (fromPage > toPage) {
                logMessage("Trang bắt đầu phải <= trang kết thúc", "error");
                return;
            }
            
            btn.disabled = true;
            logMessage(`Bắt đầu Crawl ${source} từ trang ${fromPage} đến ${toPage}...`, 'info');
            
            for (let p = fromPage; p <= toPage; p++) {
                logMessage(`Đang tải danh sách phim trang ${p} từ ${source}...`, 'warn');
                try {
                    const pData = new FormData();
                    pData.append('action', 'get_page_slugs');
                    pData.append('source', source);
                    pData.append('page', p);
                    
                    const pRes = await fetch(pluginPath, { method: 'POST', body: pData });
                    const pJson = await pRes.json();
                    
                    if (pJson.status !== 'success') {
                        logMessage(`Lỗi trang ${p}: ${pJson.message}`, 'error');
                        continue;
                    }
                    
                    const slugs = pJson.slugs || [];
                    logMessage(`Trang ${p} có ${slugs.length} phim. Bắt đầu fetch chi tiết...`, 'info');
                    
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
                                logMessage(`[Trang ${p} - ${i+1}/${slugs.length}] Đã lưu: <b>${mJson.movie_name}</b>`, 'success');
                            } else {
                                logMessage(`[Trang ${p} - ${i+1}/${slugs.length}] Lỗi (${slug}): ${mJson.message}`, 'error');
                            }
                        } catch (e) {
                            logMessage(`[Trang ${p} - ${i+1}/${slugs.length}] Lỗi mạng (${slug})`, 'error');
                        }
                    }
                } catch (e) {
                    logMessage(`Lỗi kết nối trang ${p}: ${e.message}`, 'error');
                }
            }
            logMessage(`Hoàn thành Crawl danh sách từ ${source}!`, 'success');
            btn.disabled = false;
        });
    });

    // Crawl Keyword
    document.querySelectorAll('.crawl-keyword-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const source = form.getAttribute('data-source');
            const keyword = form.keyword.value.trim();
            const limit = parseInt(form.limit.value) || 10;
            const btn = form.querySelector('button[type="submit"]');
            
            if (!keyword) return;
            btn.disabled = true;
            logMessage(`Đang tìm "${keyword}" trên ${source}...`, 'info');
            
            try {
                const fd = new FormData();
                fd.append('action', 'crawl_keyword');
                fd.append('source', source);
                fd.append('keyword', keyword);
                fd.append('limit', limit);
                
                const res = await fetch(pluginPath, { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.status !== 'success') {
                    logMessage(`Lỗi tìm kiếm: ${data.message}`, 'error');
                } else {
                    const slugs = data.slugs || [];
                    logMessage(`Tìm thấy ${slugs.length} phim. Bắt đầu tải...`, 'info');
                    for (let i = 0; i < slugs.length; i++) {
                        const slug = slugs[i];
                        const mData = new FormData();
                        mData.append('action', 'crawl_single');
                        mData.append('slug', slug);
                        mData.append('fetch_images', '0');
                        
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
                    logMessage(`Hoàn thành tải phim theo từ khóa từ ${source}!`, 'success');
                }
            } catch (e) {
                logMessage(`Lỗi kết nối: ${e.message}`, 'error');
            }
            btn.disabled = false;
        });
    });

    // Recrawl failed
    const btnRecrawlFailed = document.getElementById('btnRecrawlFailed');
    if (btnRecrawlFailed) {
        btnRecrawlFailed.addEventListener('click', async () => {
            if (failedSlugsList.length === 0) return;
            btnRecrawlFailed.disabled = true;
            logMessage(`Thử tải lại ${failedSlugsList.length} phim lỗi...`, 'warn');
            
            const slugsToProcess = [...failedSlugsList];
            for (let i = 0; i < slugsToProcess.length; i++) {
                const slug = slugsToProcess[i];
                const mData = new FormData();
                mData.append('action', 'crawl_single');
                mData.append('slug', slug);
                mData.append('fetch_images', '0');
                
                try {
                    const mRes = await fetch(pluginPath, { method: 'POST', body: mData });
                    const mJson = await mRes.json();
                    
                    if (mJson.status === 'success') {
                        logMessage(`[Phim Lỗi - ${i+1}/${slugsToProcess.length}] Tải thành công: <b>${mJson.movie_name}</b>`, 'success');
                        const rData = new FormData();
                        rData.append('action', 'remove_failed_slug');
                        rData.append('slug', slug);
                        await fetch(pluginPath, { method: 'POST', body: rData });
                        
                        failedSlugsList = failedSlugsList.filter(s => s !== slug);
                        document.getElementById('failedCount').textContent = failedSlugsList.length;
                    } else {
                        logMessage(`[Phim Lỗi - ${i+1}/${slugsToProcess.length}] Vẫn lỗi (${slug}): ${mJson.message}`, 'error');
                    }
                } catch (e) {
                    logMessage(`[Phim Lỗi - ${i+1}/${slugsToProcess.length}] Lỗi mạng (${slug})`, 'error');
                }
            }
            logMessage('Hoàn thành tải lại phim lỗi!', 'success');
            btnRecrawlFailed.disabled = failedSlugsList.length === 0;
        });
    }
});
</script>
</div>
