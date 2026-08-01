<div class="flex items-end gap-2 mb-6">
    <div class="flex-1">
        <h2 class="text-2xl font-bold text-white mb-2">Cào Dữ Liệu Phim</h2>
        <p class="text-sm text-gray-400">Trình cào dữ liệu thông minh tự nhận diện định dạng XML & JSON</p>
    </div>
    <button onclick="document.getElementById('sourceModal').classList.remove('hidden')" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-xl border border-gray-700 flex items-center text-sm transition-colors">
        <i data-lucide="settings" class="w-4 h-4 mr-2"></i> Quản Lý Nguồn
    </button>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-3xl relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-red-600/5 rounded-full blur-[80px] pointer-events-none"></div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 relative z-10">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Nguồn cào</label>
            <select id="crawlSource" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
                <option value="kkphim">KKPhim.com (PhimAPI)</option>
                <option value="ophim">Ophim1.com</option>
                <option value="nguonc">NguonC.com</option>
                <?php
                $sourcesFile = __DIR__ . '/../../config/crawl_sources.json';
                if (file_exists($sourcesFile)) {
                    $customSources = json_decode(file_get_contents($sourcesFile), true);
                    if (is_array($customSources)) {
                        foreach ($customSources as $s) {
                            echo '<option value="' . htmlspecialchars($s['id']) . '">✨ ' . htmlspecialchars($s['name']) . '</option>';
                        }
                    }
                }
                ?>
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

    // Custom Sources Management
    function loadManageSources() {
        fetch('api/manage_sources.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const list = document.getElementById('sourceList');
                    list.innerHTML = '';
                    data.sources.forEach(s => {
                        list.innerHTML += `
                            <div class="flex justify-between items-center p-3 bg-gray-900 border border-gray-700 rounded-lg mb-2">
                                <div>
                                    <h4 class="text-white font-bold text-sm">${s.name}</h4>
                                    <p class="text-xs text-gray-500 mt-1 truncate max-w-[250px]">${s.url}</p>
                                </div>
                                <button onclick="deleteSource('${s.id}')" class="text-red-500 hover:text-red-400 p-2">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        `;
                    });
                    lucide.createIcons();
                }
            });
    }

    function addSource() {
        const name = document.getElementById('newSourceName').value;
        const url = document.getElementById('newSourceUrl').value;
        if (!name || !url) return alert('Vui lòng nhập đủ thông tin');
        if (!url.includes('{page}')) return alert('URL phải chứa biến {page} để có thể phân trang');

        fetch('api/manage_sources.php', {
            method: 'POST',
            body: JSON.stringify({ name, url }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('newSourceName').value = '';
                document.getElementById('newSourceUrl').value = '';
                loadManageSources();
                alert('Thêm thành công! Vui lòng tải lại trang để thấy nguồn cào mới.');
            } else alert(data.message);
        });
    }

    function deleteSource(id) {
        if (!confirm('Bạn có chắc muốn xoá nguồn này?')) return;
        fetch('api/manage_sources.php', {
            method: 'DELETE',
            body: JSON.stringify({ id }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadManageSources();
                alert('Xoá thành công! Vui lòng tải lại trang.');
            } else alert(data.message);
        });
    }
    
    // Load sources when modal opens
    document.querySelector('[onclick="document.getElementById(\'sourceModal\').classList.remove(\'hidden\')"]').addEventListener('click', loadManageSources);
</script>

<!-- Manage Sources Modal -->
<div id="sourceModal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-5 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg">Nguồn Cào Tuỳ Chỉnh</h3>
            <button onclick="document.getElementById('sourceModal').classList.add('hidden')" class="text-gray-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-5">
            <div class="mb-5">
                <label class="block text-sm text-gray-300 mb-1">Tên nguồn</label>
                <input type="text" id="newSourceName" placeholder="Ví dụ: PhimMoi XML" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-blue-500 mb-3">
                
                <label class="block text-sm text-gray-300 mb-1">URL API (JSON/XML)</label>
                <input type="text" id="newSourceUrl" placeholder="https://domain.com/rss?page={page}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-blue-500 text-sm font-mono mb-2">
                <p class="text-xs text-gray-500 mb-3">Chú ý: Phải có biến <code class="text-yellow-400">{page}</code> trong URL để có thể phân trang.</p>
                
                <button onclick="addSource()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-bold shadow-lg shadow-blue-500/20">Thêm Nguồn Mới</button>
            </div>
            
            <div class="border-t border-gray-700 pt-5">
                <h4 class="text-gray-400 text-xs font-bold uppercase mb-3">Nguồn Đã Thêm</h4>
                <div id="sourceList" class="max-h-48 overflow-y-auto custom-scrollbar pr-2">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>
    </div>
</div>
