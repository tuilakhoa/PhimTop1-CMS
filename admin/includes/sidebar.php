        <!-- Sidebar Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden md:hidden transition-all duration-300"></div>
        
        <!-- Sidebar -->
        <div id="admin-sidebar" class="fixed top-16 bottom-0 left-0 z-40 w-64 bg-admin-panel/90 backdrop-blur-xl border-r border-admin-border flex flex-col transform -translate-x-full md:relative md:top-0 md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto custom-scrollbar shadow-2xl">
            <div class="p-4 flex-grow space-y-1">
                <?php 
                $settings_sidebar = getSettings();
                $menuGroups = [
                    'Tổng Quan' => [
                        'dashboard' => ['icon' => 'layout-dashboard', 'title' => 'Bảng Điều Khiển'],
                    ],
                    'Quản Lý Nội Dung' => [
                        'movies' => ['icon' => 'film', 'title' => 'Phim'],
                        'categories' => ['icon' => 'list-tree', 'title' => 'Thể Loại & Quốc Gia'],
                        'blocked_movies' => ['icon' => 'shield-alert', 'title' => 'Phim Đã Gỡ'],
                    ],
                    'Người Dùng & Tương Tác' => [
                        'members' => ['icon' => 'user-check', 'title' => 'Thành Viên'],
                        'roles' => ['icon' => 'shield', 'title' => 'Phân Quyền Admin'],
                        'watching_sessions' => ['icon' => 'monitor-play', 'title' => 'Người Đang Xem'],
                        'events' => ['icon' => 'gift', 'title' => 'Sự Kiện & Thưởng Xu'],
                    ],
                    'Hệ Thống & Cài Đặt' => [
                        'settings' => ['icon' => 'settings', 'title' => 'Cấu Hình Chung'],
                        'app_settings' => ['icon' => 'smartphone', 'title' => 'App & API Settings'],
                        'update' => ['icon' => 'refresh-cw', 'title' => 'Cập Nhật Phiên Bản'],
                        'plugins' => ['icon' => 'puzzle', 'title' => 'Quản Lý Plugins'],
                        'system_status' => ['icon' => 'activity', 'title' => 'Kiểm Tra Hệ Thống'],
                    ],
                    'SEO & Bảo Mật' => [
                        'security' => ['icon' => 'shield-check', 'title' => 'Bảo Mật & Đo Lường'],
                        'seo' => ['icon' => 'search', 'title' => 'Cấu Hình SEO'],
                        'seo_manager' => ['icon' => 'file-search', 'title' => 'SEO Từng Trang'],
                        'sitemap' => ['icon' => 'map', 'title' => 'Quản Lý Sitemap'],
                        'robots' => ['icon' => 'file-text', 'title' => 'Robots.txt'],
                    ]
                ];
                

                // Allow plugins to inject custom admin menus
                $menuGroups = apply_filters('admin_menu_groups', $menuGroups);
                
                foreach ($menuGroups as $groupName => $items):
                ?>
                <div class="mb-6">
                    <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-3 flex items-center gap-2">
                        <span class="w-2 h-[1px] bg-gray-600"></span>
                        <?= $groupName ?>
                    </h3>
                    <div class="space-y-1.5 px-2">
                        <?php foreach ($items as $key => $item):
                            $isActive = $currentPage === $key;
                            $classes = $isActive 
                                ? "bg-gradient-to-r from-admin-primary/20 to-transparent text-admin-primary border-l-2 border-admin-primary shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)]" 
                                : "text-gray-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent hover:border-gray-500";
                            
                            $iconClasses = $isActive
                                ? "text-admin-primary drop-shadow-[0_0_8px_rgba(244,63,94,0.5)]"
                                : "text-gray-500 group-hover:text-gray-300";
                        ?>
                        <a href="?page=<?= $key ?>" class="group flex items-center space-x-3 px-3 py-2.5 rounded-r-xl transition-all duration-300 relative overflow-hidden <?= $classes ?>">
                            <?php if($isActive): ?>
                                <div class="absolute inset-0 bg-gradient-to-r from-admin-primary/10 to-transparent opacity-50"></div>
                            <?php endif; ?>
                            <i data-lucide="<?= $item['icon'] ?>" class="w-[18px] h-[18px] relative z-10 transition-colors duration-300 <?= $iconClasses ?>"></i>
                            <span class="font-medium text-sm relative z-10 tracking-wide"><?= $item['title'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
