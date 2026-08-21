        <!-- Sidebar Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity"></div>
        
        <!-- Sidebar -->
        <div id="admin-sidebar" class="fixed top-16 bottom-0 left-0 z-40 w-64 bg-gray-900 border-r border-gray-800 flex flex-col transform -translate-x-full md:relative md:top-0 md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto custom-scrollbar">
            <div class="p-4 flex-grow space-y-1">
                <?php 
                $settings_sidebar = getSettings();
                $menuGroups = [
                    'Quản Lý Nội Dung' => [
                        'dashboard' => ['icon' => 'layout-dashboard', 'title' => 'Tổng Quan'],
                        'watch_parties' => ['icon' => 'users', 'title' => 'Phòng Xem Chung'],
                        'watching_sessions' => ['icon' => 'monitor-play', 'title' => 'Người Đang Xem'],
                        'movies' => ['icon' => 'film', 'title' => 'Quản Lý Phim'],
                        'categories' => ['icon' => 'list-tree', 'title' => 'Thể Loại & Quốc Gia'],
                        'comments' => ['icon' => 'message-square', 'title' => 'Quản Lý Bình Luận'],
                        'feedbacks' => ['icon' => 'message-circle', 'title' => 'Quản Lý Phản Hồi'],
                    ],
                    'Công Cụ' => [
                        'crawl' => ['icon' => 'download-cloud', 'title' => 'Cào Dữ Liệu'],
                        'database' => ['icon' => 'database', 'title' => 'Quản Lý Database'],
                        'theme_editor' => ['icon' => 'file-code', 'title' => 'Sửa Giao Diện'],
                        'system_status' => ['icon' => 'activity', 'title' => 'Kiểm Tra Hệ Thống'],
                        'events' => ['icon' => 'gift', 'title' => 'Sự Kiện & Thưởng Xu'],
                    ],
                    'Hệ Thống' => [
                        'members' => ['icon' => 'users', 'title' => 'Thành Viên'],
                        'settings' => ['icon' => 'settings', 'title' => 'Cấu Hình Chung'],
                        'security' => ['icon' => 'shield-check', 'title' => 'Bảo Mật & Đo Lường'],
                        'seo' => ['icon' => 'search', 'title' => 'Cấu Hình SEO'],
                        'seo_manager' => ['icon' => 'file-search', 'title' => 'SEO Từng Trang'],
                        'sitemap' => ['icon' => 'map', 'title' => 'Quản Lý Sitemap'],
                        'robots' => ['icon' => 'file-text', 'title' => 'Robots.txt'],
                        'themes' => ['icon' => 'palette', 'title' => 'Giao Diện'],
                        'update' => ['icon' => 'refresh-cw', 'title' => 'Cập Nhật Phiên Bản'],
                        'plugins' => ['icon' => 'plug', 'title' => 'Quản Lý Plugin'],
                        'app_settings' => ['icon' => 'smartphone', 'title' => 'App & API Settings']
                    ]
                ];
                

                // Allow plugins to inject custom admin menus
                $menuGroups = apply_filters('admin_menu_groups', $menuGroups);
                
                foreach ($menuGroups as $groupName => $items):
                ?>
                <div class="mb-5">
                    <h3 class="px-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2"><?= $groupName ?></h3>
                    <div class="space-y-1">
                        <?php foreach ($items as $key => $item):
                            $isActive = $currentPage === $key;
                            $classes = $isActive 
                                ? "bg-red-600/10 text-red-500" 
                                : "text-gray-400 hover:text-white hover:bg-gray-800";
                        ?>
                        <a href="?page=<?= $key ?>" class="w-full flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all <?= $classes ?>">
                            <i data-lucide="<?= $item['icon'] ?>" class="w-5 h-5"></i>
                            <span class="font-medium text-sm"><?= $item['title'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
