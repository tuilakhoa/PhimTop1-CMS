<div class="flex items-end gap-2 mb-6">
    <div class="flex-1">
        <h2 class="text-2xl font-bold text-white mb-2">Quản Lý SEO Từng Trang</h2>
        <p class="text-sm text-gray-400">Thiết lập thẻ tiêu đề, mô tả và thay đổi đường dẫn (slug) cho từng phim, thể loại, quốc gia cụ thể.</p>
    </div>
    <button onclick="openModal()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl border border-red-500 flex items-center text-sm transition-colors shadow-lg shadow-red-600/20">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Thêm Ghi Đè Mới
    </button>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-800/50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-4 font-bold">Loại Trang</th>
                    <th class="px-6 py-4 font-bold">ID/Slug Gốc</th>
                    <th class="px-6 py-4 font-bold">Slug Ảo (Custom)</th>
                    <th class="px-6 py-4 font-bold max-w-xs">Tiêu đề SEO</th>
                    <th class="px-6 py-4 font-bold text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="seoTableBody" class="divide-y divide-gray-800">
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i data-lucide="loader" class="w-6 h-6 animate-spin mx-auto mb-2"></i> Đang tải dữ liệu...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="seoModal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center bg-gray-900">
            <h3 class="text-white font-bold text-lg" id="modalTitle">Thêm Ghi Đè SEO</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
            <form id="seoForm" onsubmit="saveSeo(event)">
                <input type="hidden" id="editId">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Loại Trang <span class="text-red-500">*</span></label>
                        <select id="seoType" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
                            <option value="movie">Phim (Thông tin chung)</option>
                            <option value="watch">Trang xem phim (Tập)</option>
                            <option value="the-loai">Thể loại</option>
                            <option value="quoc-gia">Quốc gia</option>
                            <option value="list">Danh sách (Phim bộ, phim lẻ...)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">ID hoặc Slug Gốc <span class="text-red-500">*</span></label>
                        <input type="text" id="seoItemId" required placeholder="Ví dụ: avengers, hanh-dong" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
                    </div>
                </div>
                
                <div class="mb-4 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl">
                    <label class="block text-sm font-medium text-blue-400 mb-1">Đổi Đường Dẫn Ảo (Custom Slug) (Tuỳ chọn)</label>
                    <p class="text-xs text-gray-400 mb-2">Nhập slug mới nếu bạn muốn giấu slug gốc. (VD: slug gốc 'hanh-dong', slug ảo 'phim-danh-nhau' -> người dùng truy cập /the-loai/phim-danh-nhau sẽ lấy dữ liệu của hanh-dong).</p>
                    <input type="text" id="seoCustomSlug" placeholder="Để trống nếu không muốn đổi" class="w-full bg-gray-900 border border-blue-500/30 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-blue-500 font-mono text-sm">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-1">SEO Title (Tiêu đề SEO)</label>
                    <input type="text" id="seoTitle" placeholder="Tiêu đề hiển thị trên Google" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
                    <p class="text-xs text-gray-500 mt-1">Hỗ trợ biến <code class="text-yellow-400">{ep}</code> cho trang Xem Phim để hiển thị tập hiện tại.</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-1">SEO Description (Mô tả SEO)</label>
                    <textarea id="seoDesc" rows="3" placeholder="Mô tả nội dung trang (Nên dưới 160 ký tự)" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-1">SEO Keywords (Từ khoá SEO)</label>
                    <input type="text" id="seoKeywords" placeholder="Từ khoá 1, từ khoá 2..." class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-1 focus:ring-red-500">
                </div>
            </form>
        </div>
        <div class="p-5 border-t border-gray-800 bg-gray-900 flex justify-end gap-3">
            <button onclick="closeModal()" type="button" class="px-5 py-2 text-gray-300 hover:text-white font-medium">Hủy</button>
            <button onclick="document.getElementById('seoForm').requestSubmit()" type="button" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium shadow-lg shadow-red-600/20">
                Lưu Thay Đổi
            </button>
        </div>
    </div>
</div>

<script>
    let seoData = [];

    function typeToLabel(type) {
        const map = {
            'movie': '<span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded text-xs">Phim</span>',
            'watch': '<span class="bg-purple-500/20 text-purple-400 px-2 py-1 rounded text-xs">Xem Phim</span>',
            'the-loai': '<span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs">Thể Loại</span>',
            'quoc-gia': '<span class="bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded text-xs">Quốc Gia</span>',
            'list': '<span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs">Danh Sách</span>'
        };
        return map[type] || type;
    }

    async function loadData() {
        const res = await fetch('api/seo_metadata.php');
        const data = await res.json();
        seoData = data.data || [];
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('seoTableBody');
        if (seoData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Chưa có ghi đè SEO nào. Hãy bấm "Thêm Ghi Đè Mới"</td></tr>`;
            return;
        }

        tbody.innerHTML = seoData.map(item => `
            <tr class="hover:bg-gray-800/50 transition-colors">
                <td class="px-6 py-4">${typeToLabel(item.type)}</td>
                <td class="px-6 py-4 font-mono text-sm text-gray-400">${item.item_id}</td>
                <td class="px-6 py-4">
                    ${item.custom_slug ? `<span class="text-blue-400 font-mono text-sm">${item.custom_slug}</span>` : '<span class="text-gray-600 text-xs italic">Không</span>'}
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium text-white truncate max-w-xs">${item.seo_title || '<span class="text-gray-500 italic">Mặc định</span>'}</div>
                    <div class="text-xs text-gray-500 truncate max-w-xs mt-1">${item.seo_desc || ''}</div>
                </td>
                <td class="px-6 py-4 text-right">
                    <button onclick="editSeo(${item.id})" class="text-blue-400 hover:text-blue-300 p-2 mr-1" title="Sửa">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    <button onclick="deleteSeo(${item.id})" class="text-red-400 hover:text-red-300 p-2" title="Xoá">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }

    function openModal() {
        document.getElementById('editId').value = '';
        document.getElementById('seoForm').reset();
        document.getElementById('modalTitle').textContent = 'Thêm Ghi Đè SEO';
        document.getElementById('seoModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('seoModal').classList.add('hidden');
    }

    function editSeo(id) {
        const item = seoData.find(i => i.id === id);
        if (!item) return;

        document.getElementById('editId').value = item.id;
        document.getElementById('seoType').value = item.type;
        document.getElementById('seoItemId').value = item.item_id;
        document.getElementById('seoCustomSlug').value = item.custom_slug || '';
        document.getElementById('seoTitle').value = item.seo_title || '';
        document.getElementById('seoDesc').value = item.seo_desc || '';
        document.getElementById('seoKeywords').value = item.seo_keywords || '';

        document.getElementById('modalTitle').textContent = 'Sửa Ghi Đè SEO';
        document.getElementById('seoModal').classList.remove('hidden');
    }

    async function saveSeo(e) {
        e.preventDefault();
        
        const payload = {
            id: document.getElementById('editId').value,
            type: document.getElementById('seoType').value,
            item_id: document.getElementById('seoItemId').value,
            custom_slug: document.getElementById('seoCustomSlug').value,
            seo_title: document.getElementById('seoTitle').value,
            seo_desc: document.getElementById('seoDesc').value,
            seo_keywords: document.getElementById('seoKeywords').value
        };

        const res = await fetch('api/seo_metadata.php', {
            method: 'POST',
            body: JSON.stringify(payload),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        if (data.success) {
            closeModal();
            loadData();
        } else {
            alert(data.message);
        }
    }

    async function deleteSeo(id) {
        if (!confirm('Bạn có chắc muốn xoá ghi đè này không? Cấu hình sẽ trở về mặc định.')) return;
        
        const res = await fetch('api/seo_metadata.php', {
            method: 'DELETE',
            body: JSON.stringify({ id }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            loadData();
        } else {
            alert(data.message);
        }
    }

    // Init
    loadData();
</script>
