<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Update Server</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-effect:hover {
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-300 min-h-screen relative overflow-x-hidden font-sans">
    
    <!-- Ambient Background Effects -->
    <div class="fixed top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-blue-600/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-purple-600/10 rounded-full blur-[100px] pointer-events-none z-0"></div>

    <!-- Header -->
    <header class="glass-panel sticky top-0 z-30 border-b border-gray-800/50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 transform hover:scale-110 transition-transform">
                        <i data-lucide="server" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400 tracking-tight">Update Server API</span>
                </div>
                <div>
                    <a href="/admin/logout" class="flex items-center gap-2 px-4 py-2 bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition-all text-sm font-medium border border-red-500/20">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'created'): ?>
            <div class="mb-8 bg-green-500/10 border border-green-500/30 text-green-400 p-5 rounded-2xl flex items-center shadow-lg shadow-green-500/5 animate-fade-in-down">
                <div class="bg-green-500/20 p-2 rounded-lg mr-4">
                    <i data-lucide="check-circle" class="w-6 h-6 flex-shrink-0"></i>
                </div>
                <div>
                    <h4 class="font-bold">Thành công!</h4>
                    <p class="text-sm opacity-90">Đã phát hành phiên bản mới lên hệ thống.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="mb-8 bg-red-500/10 border border-red-500/30 text-red-400 p-5 rounded-2xl flex items-center shadow-lg shadow-red-500/5 animate-fade-in-down">
                <div class="bg-red-500/20 p-2 rounded-lg mr-4">
                    <i data-lucide="trash-2" class="w-6 h-6 flex-shrink-0"></i>
                </div>
                <div>
                    <h4 class="font-bold">Đã xóa!</h4>
                    <p class="text-sm opacity-90">Phiên bản đã được gỡ khỏi hệ thống thành công.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Tạo Phiên Bản -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-3xl p-6 lg:sticky lg:top-24 shadow-2xl relative overflow-hidden group">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/10 rounded-full blur-[40px] pointer-events-none group-hover:bg-blue-500/20 transition-all duration-500"></div>
                    
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-400">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </div>
                        Tạo Phiên Bản Mới
                    </h2>
                    
                    <form action="/admin/release/create" method="POST" enctype="multipart/form-data" class="space-y-4 relative z-10">
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-1.5">Phiên bản (Vd: 1.1.0)</label>
                            <input type="text" name="version" required class="w-full bg-gray-900/80 border border-gray-700/50 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-gray-600" placeholder="VD: 1.2.5">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-1.5">Tiêu đề</label>
                            <input type="text" name="title" required class="w-full bg-gray-900/80 border border-gray-700/50 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-gray-600" placeholder="Bản cập nhật giao diện mới">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-1.5">Ngày phát hành</label>
                            <input type="date" name="release_date" value="<?= date('Y-m-d') ?>" required class="w-full bg-gray-900/80 border border-gray-700/50 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all [color-scheme:dark]">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-1.5">Mô tả ngắn</label>
                            <textarea name="description" rows="3" class="w-full bg-gray-900/80 border border-gray-700/50 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-gray-600" placeholder="Những thay đổi chính trong bản cập nhật này..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-1.5">Link Changelog (Tùy chọn)</label>
                            <input type="url" name="changelog" class="w-full bg-gray-900/80 border border-gray-700/50 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-gray-600" placeholder="https://github.com/...">
                        </div>

                        <div class="pt-2">
                            <label class="block text-sm font-semibold text-gray-300 mb-1.5">JSON Download URL</label>
                            <input type="url" name="download_url" required placeholder="https://raw.githubusercontent.com/.../update.json" class="w-full bg-gray-900/80 border border-gray-700/50 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                            <p class="text-xs text-gray-500 mt-2">Đường dẫn tới file update.json khai báo danh sách changed_files.</p>
                        </div>

                        <div class="flex items-center justify-between pt-4 pb-2">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="is_force" value="1" class="peer sr-only">
                                    <div class="w-10 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-300 group-hover:text-white transition-colors">Bắt buộc cập nhật</span>
                            </label>
                            
                            <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-sm font-medium text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 cursor-pointer">
                                <option value="published">Phát hành</option>
                                <option value="draft">Bản nháp</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-blue-500/25 mt-4 flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                            <i data-lucide="zap" class="w-5 h-5"></i> Triển Khai Phiên Bản
                        </button>
                    </form>
                </div>
            </div>

            <!-- Danh sách Phiên Bản -->
            <div class="lg:col-span-2">
                <div class="glass-panel rounded-3xl p-6 shadow-2xl overflow-hidden relative">
                    <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-purple-500/5 rounded-full blur-[60px] pointer-events-none"></div>
                    
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3 relative z-10">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-400">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        Lịch Sử Quản Lý
                    </h2>
                    
                    <div class="overflow-x-auto relative z-10 rounded-2xl border border-gray-800/50 bg-gray-900/30">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-800 bg-gray-900/50">
                                    <th class="py-4 px-5 font-semibold">Phiên bản</th>
                                    <th class="py-4 px-5 font-semibold">Trạng thái</th>
                                    <th class="py-4 px-5 font-semibold">Loại</th>
                                    <th class="py-4 px-5 font-semibold">Ngày phát hành</th>
                                    <th class="py-4 px-5 font-semibold text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-800/50">
                                <?php if (empty($releases)): ?>
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-gray-500">
                                            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                            <p>Chưa có phiên bản nào được phát hành.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($releases as $release): ?>
                                        <tr class="hover:bg-gray-800/40 transition-colors group">
                                            <td class="py-4 px-5">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center border border-gray-700 shadow-inner group-hover:border-gray-600 transition-colors">
                                                        <i data-lucide="package" class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-black text-white text-base tracking-tight">v<?= htmlspecialchars($release['version']) ?></div>
                                                        <div class="text-xs text-gray-500 truncate max-w-[180px] mt-0.5" title="<?= htmlspecialchars($release['title']) ?>"><?= htmlspecialchars($release['title']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-5">
                                                <?php if ($release['status'] === 'published'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20 shadow-[0_0_10px_rgba(34,197,94,0.1)]">
                                                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Public
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-500/10 text-gray-400 border border-gray-500/20">
                                                        <span class="w-2 h-2 rounded-full bg-gray-500"></span> Draft
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-5">
                                                <?php if ($release['is_force']): ?>
                                                    <span class="text-red-400 text-xs font-bold bg-red-500/10 px-2.5 py-1 rounded-md border border-red-500/20 flex items-center w-max gap-1">
                                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Bắt buộc
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-500 text-xs font-medium px-2.5 py-1 bg-gray-800/50 rounded-md">Thường</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-5 text-gray-400 font-medium">
                                                <?= date('d/m/Y', strtotime($release['release_date'])) ?>
                                            </td>
                                            <td class="py-4 px-5 text-right">
                                                <div class="flex items-center justify-end gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                                    <?php if ($release['download_url']): ?>
                                                        <a href="<?= htmlspecialchars($release['download_url']) ?>" target="_blank" class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-xl transition-all" title="Tải xuống">
                                                            <i data-lucide="download-cloud" class="w-4 h-4"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="/admin/release/delete?id=<?= $release['id'] ?>" onclick="return confirm('Cảnh báo: Hành động này không thể hoàn tác. Bạn có chắc muốn xóa phiên bản này?');" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all" title="Xóa phiên bản">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
