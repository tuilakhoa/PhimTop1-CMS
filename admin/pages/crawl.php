<h2 class="text-2xl font-bold text-white mb-6">Cào Dữ Liệu Phim</h2>
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-3xl">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Nguồn cào</label>
            <select id="crawlSource" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
                <option value="kkphim">KKPhim.com (PhimAPI)</option>
                <option value="ophim">Ophim1.com</option>
                <option value="nguonc">NguonC.com</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Từ trang</label>
            <input type="number" id="crawlPageFrom" value="1" min="1" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Đến trang</label>
            <input type="number" id="crawlPageTo" value="10" min="1" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
        </div>
    </div>
    
    <div class="mb-6 p-4 bg-gray-800 rounded-xl border border-gray-700">
        <label class="flex items-center text-white cursor-pointer select-none">
            <input type="checkbox" id="downloadImages" class="mr-3 w-5 h-5 accent-red-600 rounded bg-gray-700 border-gray-600"> Tải ảnh về Server (Lưu local, convert tự động sang định dạng WebP)
        </label>
        <div id="imageOptions" class="hidden grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-700">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Resize Thumb Width (px)</label>
                <input type="number" id="thumbWidth" value="300" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500" placeholder="vd: 300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Resize Poster Width (px)</label>
                <input type="number" id="posterWidth" value="500" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500" placeholder="vd: 500">
            </div>
        </div>
    </div>
    
    <button onclick="startCrawl()" id="btnCrawl" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-red-600/20 flex items-center justify-center">
        <i data-lucide="play" class="w-5 h-5 mr-2"></i> Bắt Đầu Cào Phim
    </button>
</div>

<div id="crawlLogContainer" class="mt-8 hidden">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-bold text-white flex items-center"><i data-lucide="terminal" class="w-5 h-5 mr-2 text-green-400"></i> Tiến Trình Cào</h3>
    </div>
    <div class="bg-[#0c0c0c] border border-gray-800 rounded-xl p-4 font-mono text-sm h-96 overflow-y-auto custom-scrollbar shadow-inner" id="crawlLog">
        <!-- Logs will appear here -->
    </div>
</div>

<script>
    async function startCrawl() {
        const source = document.getElementById('crawlSource').value;
        const from = parseInt(document.getElementById('crawlPageFrom').value);
        const to = parseInt(document.getElementById('crawlPageTo').value);
        const dl = document.getElementById('downloadImages').checked ? 1 : 0;
        const tw = document.getElementById('thumbWidth').value;
        const pw = document.getElementById('posterWidth').value;
        const logContainer = document.getElementById('crawlLogContainer');
        const logEl = document.getElementById('crawlLog');
        const btn = document.getElementById('btnCrawl');

        if (from > to) {
            alert("Trang bắt đầu phải nhỏ hơn hoặc bằng trang kết thúc!");
            return;
        }

        logContainer.classList.remove('hidden');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Đang cào...';
        lucide.createIcons();
        logEl.innerHTML = '';

        const appendLog = (msg, type = 'info') => {
            const color = type === 'error' ? 'text-red-500' : type === 'success' ? 'text-green-500' : 'text-gray-300';
            const icon = type === 'error' ? '✖' : type === 'success' ? '✔' : '➜';
            logEl.innerHTML += `<div class="${color} mb-1"><span class="opacity-50 mr-2">[${new Date().toLocaleTimeString()}]</span> ${icon} ${msg}</div>`;
            logEl.scrollTop = logEl.scrollHeight;
        };

        for (let page = from; page <= to; page++) {
            appendLog(`Đang cào trang ${page}...`, 'info');
            try {
                const res = await fetch(`api/crawl_process.php?page=${page}&source=${source}&dl=${dl}&tw=${tw}&pw=${pw}`);
                const data = await res.json();
                
                if (data.status === 'success') {
                    appendLog(`Trang ${page}: Thêm ${data.added}, Cập nhật ${data.updated}`, 'success');
                } else {
                    appendLog(`Lỗi trang ${page}: ${data.message}`, 'error');
                }
            } catch (err) {
                appendLog(`Lỗi kết nối trang ${page}: ${err.message}`, 'error');
            }
        }

        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="play" class="w-5 h-5 mr-2"></i> Bắt Đầu Cào Lại';
        lucide.createIcons();
        appendLog('✅ HOÀN TẤT QUÁ TRÌNH CÀO DỮ LIỆU!', 'success');
    }
    
    document.getElementById('downloadImages').addEventListener('change', function() {
        document.getElementById('imageOptions').classList.toggle('hidden', !this.checked);
    });
</script>
