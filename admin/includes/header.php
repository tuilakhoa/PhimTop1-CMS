<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PhimTop1</title>
    <link rel="stylesheet" href="/assets/css/style.min.css">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($settings['adminPath'] ?? '/admin') ?>/assets/css/admin.css?v=<?= time() ?>">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="PhimTop1 Admin">
    <link rel="manifest" href="/site.webmanifest">
    
    <?php if (!empty($settings['faviconUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['faviconUrl']) ?>">
    <?php elseif (!empty($settings['logoUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['logoUrl']) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <?php endif; ?>
</head>
<body class="bg-admin-bg text-gray-200 min-h-screen font-sans selection:bg-admin-primary selection:text-white relative bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-900 via-admin-bg to-admin-bg">
    
    <nav class="bg-admin-panel backdrop-blur-md border-b border-admin-border sticky top-0 z-50 transition-all duration-300">
        <div class="px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white transition-colors focus:outline-none p-2 rounded-lg hover:bg-white/5">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <a href="/" class="flex items-center space-x-3 text-white group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-admin-primary to-rose-700 flex items-center justify-center shadow-[0_0_15px_var(--tw-shadow-color)] shadow-admin-primaryGlow group-hover:scale-105 transition-transform duration-300">
                        <i data-lucide="play" class="w-5 h-5 fill-white text-white"></i>
                    </div>
                    <div class="hidden sm:flex flex-col">
                        <span class="font-bold text-lg leading-tight tracking-wide bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">PhimTop1</span>
                        <span class="text-[10px] text-admin-primary uppercase tracking-widest font-semibold">Workspace</span>
                    </div>
                </a>
            </div>
            
            <!-- Global Search Button -->
            <div class="hidden md:flex flex-1 max-w-md mx-8">
                <button onclick="openGlobalSearch()" class="w-full flex items-center justify-between bg-black/20 hover:bg-black/40 border border-gray-700/50 hover:border-gray-500/50 text-gray-400 px-4 py-2 rounded-xl transition-all duration-300 backdrop-blur-sm group">
                    <div class="flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4 text-gray-500 group-hover:text-admin-primary transition-colors"></i>
                        <span class="text-sm">Tìm kiếm (Cài đặt, Menu)...</span>
                    </div>
                    <kbd class="hidden sm:inline-block px-2 py-0.5 text-xs font-semibold text-gray-500 bg-gray-800/50 border border-gray-700 rounded-md">Ctrl K</kbd>
                </button>
            </div>

            <div class="flex items-center space-x-3">
                <a href="/" target="_blank" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2 border border-transparent hover:border-white/10">
                    <i data-lucide="external-link" class="w-4 h-4"></i> <span class="hidden sm:inline">Xem Web</span>
                </a>
                <a href="?action=logout" class="px-4 py-2 rounded-lg text-sm font-medium text-rose-400 hover:text-white hover:bg-rose-500 hover:shadow-[0_0_15px_var(--tw-shadow-color)] hover:shadow-rose-500/30 transition-all flex items-center gap-2 border border-rose-500/20 hover:border-transparent">
                    <i data-lucide="log-out" class="w-4 h-4"></i> <span class="hidden sm:inline">Đăng xuất</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Global Search Modal -->
    <div id="globalSearchModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] hidden flex items-start justify-center pt-[10vh] px-4 opacity-0 transition-opacity duration-300">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="globalSearchContent">
            <div class="p-4 border-b border-gray-800 flex items-center gap-3">
                <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
                <input type="text" id="globalSearchInput" class="w-full bg-transparent border-none text-white text-lg focus:outline-none focus:ring-0 placeholder-gray-500" placeholder="Tìm kiếm...">
                <button onclick="closeGlobalSearch()" class="text-gray-500 hover:text-white px-2 py-1 text-sm bg-gray-800 rounded-md">ESC</button>
            </div>
            <div id="globalSearchResults" class="max-h-[60vh] overflow-y-auto p-2 custom-scrollbar">
                <!-- Results will be injected here via JS -->
            </div>
            <div class="px-4 py-3 border-t border-gray-800 bg-black/20 text-xs text-gray-500 flex justify-between">
                <span>Dùng mũi tên <kbd class="px-1.5 py-0.5 bg-gray-800 rounded mx-1">↑</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 rounded mx-1">↓</kbd> để di chuyển, <kbd class="px-1.5 py-0.5 bg-gray-800 rounded mx-1">Enter</kbd> để chọn</span>
            </div>
        </div>
    </div>

    <script>
        const globalSearchData = [
            { title: "Bảng Điều Khiển (Dashboard)", url: "?page=dashboard", icon: "layout-dashboard", keywords: "tong quan dashboard" },
            { title: "Quản Lý Phim", url: "?page=movies", icon: "film", keywords: "phim movie video kho" },
            { title: "Thể Loại & Quốc Gia", url: "?page=categories", icon: "list-tree", keywords: "the loai quoc gia category tag" },
            { title: "Phim Đã Gỡ", url: "?page=blocked_movies", icon: "shield-alert", keywords: "go xoa block phim" },
            { title: "Thành Viên", url: "?page=members", icon: "user-check", keywords: "thanh vien user member" },
            { title: "Phân Quyền Admin", url: "?page=roles", icon: "shield", keywords: "phan quyen role admin" },
            { title: "Người Đang Xem", url: "?page=watching_sessions", icon: "monitor-play", keywords: "nguoi dang xem watching session online" },
            { title: "Sự Kiện & Thưởng Xu", url: "?page=events", icon: "gift", keywords: "su kien event thuong xu" },
            { title: "Cấu Hình Chung", url: "?page=settings", icon: "settings", keywords: "cau hinh cai dat settings general" },
            { title: "Cấu Hình SMTP (Gửi Email)", url: "?page=settings#tab-smtp", icon: "mail", keywords: "smtp email gui mail quen mat khau" },
            { title: "Cấu Hình Database", url: "?page=settings#tab-database", icon: "database", keywords: "database db mysql firestore" },
            { title: "Giao Diện Trang Chủ", url: "?page=settings#tab-theme", icon: "monitor", keywords: "giao dien trang chu banner home" },
            { title: "App & API Settings", url: "?page=app_settings", icon: "smartphone", keywords: "app api mobile" },
            { title: "Cập Nhật Phiên Bản", url: "?page=update", icon: "refresh-cw", keywords: "cap nhat update ban moi version" },
            { title: "Kiểm Tra Hệ Thống", url: "?page=system_status", icon: "activity", keywords: "kiem tra he thong status ping check" },
            { title: "Bảo Mật & Đo Lường", url: "?page=security", icon: "shield-check", keywords: "bao mat analytics do luong security" },
            { title: "Cấu Hình SEO", url: "?page=seo", icon: "search", keywords: "seo the title description" },
            { title: "Quản Lý Sitemap", url: "?page=sitemap", icon: "map", keywords: "sitemap xml" },
            { title: "Robots.txt", url: "?page=robots", icon: "file-text", keywords: "robots bot spider" }
        ];

        let searchSelectedIndex = -1;
        const searchModal = document.getElementById('globalSearchModal');
        const searchInput = document.getElementById('globalSearchInput');
        const resultsDiv = document.getElementById('globalSearchResults');

        function openGlobalSearch() {
            searchModal.classList.remove('hidden');
            setTimeout(() => {
                searchModal.classList.remove('opacity-0');
                document.getElementById('globalSearchContent').classList.remove('scale-95');
                searchInput.focus();
                searchInput.value = '';
                renderSearchResults('');
            }, 10);
        }

        function closeGlobalSearch() {
            searchModal.classList.add('opacity-0');
            document.getElementById('globalSearchContent').classList.add('scale-95');
            setTimeout(() => {
                searchModal.classList.add('hidden');
            }, 300);
        }

        function renderSearchResults(query) {
            query = query.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            let matches = globalSearchData;
            
            if (query) {
                matches = globalSearchData.filter(item => {
                    const normTitle = item.title.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    return normTitle.includes(query) || item.keywords.includes(query);
                });
            }

            if (matches.length === 0) {
                resultsDiv.innerHTML = '<div class="p-4 text-center text-gray-500">Không tìm thấy kết quả phù hợp</div>';
                searchSelectedIndex = -1;
                return;
            }

            searchSelectedIndex = 0;
            resultsDiv.innerHTML = matches.map((item, idx) => `
                <a href="${item.url}" onclick="if(item.url.includes('#')) { setTimeout(()=>window.location.reload(), 100); }" class="search-item block flex items-center gap-3 p-3 rounded-xl transition-all ${idx === 0 ? 'bg-admin-primary/20 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white'}">
                    <i data-lucide="${item.icon}" class="w-5 h-5 ${idx === 0 ? 'text-admin-primary' : 'text-gray-500'}"></i>
                    <span class="font-medium">${item.title}</span>
                </a>
            `).join('');
            
            if(typeof lucide !== 'undefined') lucide.createIcons();
        }

        if(searchInput) {
            searchInput.addEventListener('input', (e) => {
                renderSearchResults(e.target.value);
            });

            searchInput.addEventListener('keydown', (e) => {
                const items = resultsDiv.querySelectorAll('.search-item');
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    searchSelectedIndex = (searchSelectedIndex + 1) % items.length;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    searchSelectedIndex = (searchSelectedIndex - 1 + items.length) % items.length;
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (searchSelectedIndex >= 0) {
                        items[searchSelectedIndex].click();
                    }
                } else {
                    return;
                }

                items.forEach((item, idx) => {
                    const icon = item.querySelector('i');
                    if (idx === searchSelectedIndex) {
                        item.className = 'search-item block flex items-center gap-3 p-3 rounded-xl transition-all bg-admin-primary/20 text-white';
                        if(icon) { icon.classList.remove('text-gray-500'); icon.classList.add('text-admin-primary'); }
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.className = 'search-item block flex items-center gap-3 p-3 rounded-xl transition-all text-gray-400 hover:bg-white/5 hover:text-white';
                        if(icon) { icon.classList.remove('text-admin-primary'); icon.classList.add('text-gray-500'); }
                    }
                });
            });
        }

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                openGlobalSearch();
            }
            if (e.key === 'Escape' && searchModal && !searchModal.classList.contains('hidden')) {
                closeGlobalSearch();
            }
        });
        
        if(searchModal) {
            searchModal.addEventListener('click', (e) => {
                if (e.target === searchModal) closeGlobalSearch();
            });
        }
    </script>

    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
