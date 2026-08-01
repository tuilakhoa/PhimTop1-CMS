<h2 class="text-2xl font-bold text-white mb-6">Quản Lý Landing Page (CMS)</h2>

<div class="mb-6 border-b border-gray-800">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs" id="landing-tabs">
        <button type="button" onclick="switchLandingTab('guides')" class="landing-tab-btn active-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors" data-tab="guides">
            <i data-lucide="book-open" class="w-4 h-4 inline-block mr-2"></i>Bài Viết Hướng Dẫn
        </button>
    </nav>
</div>

<!-- Tab: Guides -->
<div id="tab-guides" class="landing-tab-content block animate-fade-in">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-white">Danh sách bài viết hướng dẫn</h3>
        <button onclick="openEditor('')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tạo Bài Mới
        </button>
    </div>
    
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden shadow-xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800/50 border-b border-gray-800">
                    <th class="p-4 text-gray-400 font-medium text-sm">Tên File (Slug)</th>
                    <th class="p-4 text-gray-400 font-medium text-sm">Hành Động</th>
                </tr>
            </thead>
            <tbody id="guide-list" class="divide-y divide-gray-800">
                <!-- XHR loaded -->
            </tbody>
        </table>
    </div>
</div>

<!-- Editor Modal -->
<div id="editor-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col transform scale-95 transition-transform" id="editor-content">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white" id="editor-title">Tạo / Sửa Bài Viết</h3>
            <button onclick="closeEditor()" class="text-gray-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6 flex-grow overflow-y-auto custom-scrollbar">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Tên File / Đường dẫn (VD: <span class="text-red-400">cai-dat-zalo</span>)</label>
                <input type="text" id="guide-slug" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none">
                <p class="text-xs text-gray-500 mt-1">Đừng gõ đuôi .html, hệ thống sẽ tự thêm.</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Nội dung HTML</label>
                <textarea id="guide-content" rows="15" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-blue-500 outline-none font-mono text-sm leading-relaxed custom-scrollbar" placeholder="Nhập HTML cho bài viết..."></textarea>
            </div>
        </div>
        <div class="p-4 border-t border-gray-800 flex justify-end gap-3 bg-gray-900/50 rounded-b-2xl">
            <button onclick="closeEditor()" class="px-5 py-2.5 rounded-lg text-gray-300 hover:bg-gray-800 font-medium transition-colors">Hủy</button>
            <button onclick="saveGuide()" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors shadow-lg shadow-blue-500/20 flex items-center">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Bài Viết
            </button>
        </div>
    </div>
</div>

<script>
function switchLandingTab(tabId) {
    document.querySelectorAll('.landing-tab-btn').forEach(btn => {
        if (btn.dataset.tab === tabId) {
            btn.classList.add('active-tab', 'text-blue-500', 'border-blue-500');
            btn.classList.remove('inactive-tab', 'text-gray-400', 'border-transparent');
        } else {
            btn.classList.remove('active-tab', 'text-blue-500', 'border-blue-500');
            btn.classList.add('inactive-tab', 'text-gray-400', 'border-transparent');
        }
    });

    document.querySelectorAll('.landing-tab-content').forEach(content => {
        if (content.id === `tab-${tabId}`) {
            content.classList.remove('hidden');
            content.classList.add('block');
        } else {
            content.classList.add('hidden');
            content.classList.remove('block');
        }
    });
}

function loadGuides() {
    fetch('api/landing_api.php?action=list')
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                const tbody = document.getElementById('guide-list');
                tbody.innerHTML = '';
                data.files.forEach(file => {
                    tbody.innerHTML += `
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="p-4 text-white font-mono text-sm">${file}.html</td>
                            <td class="p-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditor('${file}')" class="bg-gray-800 hover:bg-gray-700 text-gray-300 p-2 rounded-lg transition-colors" title="Sửa">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="deleteGuide('${file}')" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 p-2 rounded-lg transition-colors" title="Xóa">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <a href="/cms_landing_page/guide.php?slug=${file}" target="_blank" class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-500 p-2 rounded-lg transition-colors" title="Xem">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                lucide.createIcons();
            }
        });
}

function openEditor(slug) {
    const modal = document.getElementById('editor-modal');
    const content = document.getElementById('editor-content');
    modal.classList.remove('hidden');
    // small delay for transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }, 10);
    
    if (slug) {
        document.getElementById('editor-title').innerText = 'Sửa Bài Viết';
        document.getElementById('guide-slug').value = slug;
        document.getElementById('guide-slug').disabled = true; // Không cho sửa tên file nếu đang edit
        fetch(`api/landing_api.php?action=read&slug=${slug}`)
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    document.getElementById('guide-content').value = data.content;
                }
            });
    } else {
        document.getElementById('editor-title').innerText = 'Tạo Bài Mới';
        document.getElementById('guide-slug').value = '';
        document.getElementById('guide-slug').disabled = false;
        document.getElementById('guide-content').value = '';
    }
}

function closeEditor() {
    const modal = document.getElementById('editor-modal');
    const content = document.getElementById('editor-content');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function saveGuide() {
    const slug = document.getElementById('guide-slug').value;
    const content = document.getElementById('guide-content').value;
    
    if (!slug) return alert('Vui lòng nhập tên file!');
    
    fetch('api/landing_api.php?action=save', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `slug=${encodeURIComponent(slug)}&content=${encodeURIComponent(content)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            closeEditor();
            loadGuides();
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
}

function deleteGuide(slug) {
    if (confirm(`Bạn có chắc muốn xoá bài viết ${slug}.html không?`)) {
        fetch(`api/landing_api.php?action=delete&slug=${slug}`)
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    loadGuides();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            });
    }
}

// Initial load
switchLandingTab('guides');
loadGuides();
</script>
