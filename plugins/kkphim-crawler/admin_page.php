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
<div class="mt-6 bg-black/80 rounded-xl border border-admin-border overflow-hidden flex flex-col h-[400px]">
    <div class="flex items-center justify-between px-4 py-3 border-b border-admin-border bg-admin-panel">
        <h3 class="text-sm font-bold text-gray-300 flex items-center gap-2">
            <i data-lucide="terminal" class="w-4 h-4 text-green-400"></i> Tiến Trình Crawl
        </h3>
        <button id="clearLogBtn" class="text-xs text-gray-500 hover:text-white transition-colors">Xóa Log</button>
    </div>
    <div id="crawlLog" class="p-4 flex-1 overflow-y-auto font-mono text-sm space-y-1 text-gray-300">
        <div class="text-gray-500 italic">Sẵn sàng...</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    const logEl = document.getElementById('crawlLog');
    
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

    document.getElementById('clearLogBtn').addEventListener('click', () => {
        logEl.innerHTML = '';
    });

    const pluginPath = '/plugins/kkphim-crawler/ajax.php';

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
