<div class="max-w-6xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8 relative z-10">
        <div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-1 drop-shadow-sm">Cập Nhật Hệ Thống</h2>
            <p class="text-gray-400 text-sm font-medium">Cấu hình nguồn cập nhật, cài đặt tự động hoặc tải lên file thủ công.</p>
        </div>
        <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30 transform hover:rotate-12 transition-transform duration-300">
            <i data-lucide="refresh-cw" class="w-7 h-7"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
        <!-- Main Update Column -->
        <div class="xl:col-span-2 space-y-8">
            <!-- Status Card -->
            <div class="relative bg-gray-900/80 backdrop-blur-xl p-8 rounded-3xl border border-gray-800/50 shadow-2xl overflow-hidden group">
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/20 rounded-full blur-[80px] pointer-events-none group-hover:bg-blue-500/30 transition-colors duration-700"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/20 rounded-full blur-[80px] pointer-events-none group-hover:bg-purple-500/30 transition-colors duration-700"></div>
                
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 relative z-10">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                            <p class="text-gray-400 font-medium text-sm tracking-wide uppercase">Phiên Bản CMS Hiện Tại</p>
                        </div>
                        
                        <div id="update-status-container" class="mt-2">
                            <h3 class="text-4xl font-black text-white mb-3 tracking-tight" id="cms-version-display">Đang kiểm tra...</h3>
                            <div id="update-message" class="text-sm text-gray-300 mb-6 min-h-[40px] transition-all duration-300">
                                <div class="flex items-center gap-2 text-gray-400">
                                    <i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Vui lòng đợi trong giây lát...
                                </div>
                            </div>
                            
                            <button id="btn-check-update" class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold rounded-xl transition-all duration-300 flex items-center gap-2 border border-gray-700 hover:border-gray-600 shadow-md">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Tải Lại Thông Tin
                            </button>
                        </div>
                    </div>
                    
                    <div class="hidden md:flex w-24 h-24 bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl items-center justify-center border border-gray-700/50 shadow-inner shrink-0 relative">
                        <div class="absolute inset-0 bg-blue-500/10 rounded-3xl blur-md"></div>
                        <i data-lucide="server" class="w-10 h-10 text-gray-300 relative z-10"></i>
                    </div>
                </div>
            </div>

            <!-- Terminal Update Log -->
            <div id="update-log-container" class="hidden relative rounded-2xl border border-gray-800 shadow-2xl overflow-hidden backdrop-blur-md bg-gray-950/90 transition-all duration-500 opacity-0 transform translate-y-4">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-900 border-b border-gray-800">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.5)]"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500 shadow-[0_0_5px_rgba(245,158,11,0.5)]"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></div>
                    </div>
                    <span class="text-gray-500 font-mono text-[11px] font-semibold tracking-wider uppercase">System Updater (File Sync)</span>
                    <div class="w-10"></div>
                </div>
                
                <div class="px-4 pt-4 pb-2">
                    <div class="w-full bg-gray-900 rounded-full h-2 mb-4 overflow-hidden border border-gray-800">
                        <div id="update-progress-bar" class="bg-gradient-to-r from-blue-500 to-purple-500 h-full rounded-full transition-all duration-500 relative" style="width: 0%">
                            <div class="absolute top-0 right-0 bottom-0 w-20 bg-gradient-to-r from-transparent to-white/30 animate-shimmer"></div>
                        </div>
                    </div>
                    
                    <div id="update-log" class="h-64 overflow-y-auto custom-scrollbar space-y-2 pr-2 font-mono text-sm leading-relaxed pb-4">
                        <!-- Logs injected here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Tools -->
        <div class="space-y-8">
            <!-- Server Config -->
            <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-800 pb-3 flex items-center gap-2">
                    <i data-lucide="link" class="w-5 h-5 text-blue-500"></i> Nguồn Cập Nhật
                </h3>
                <form method="POST" class="space-y-4" action="?page=update">
                    <input type="hidden" name="action" value="update_settings">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Update Server (JSON URL)</label>
                        <input type="text" name="updateServerUrl" value="<?= htmlspecialchars($settings['updateServerUrl'] ?? 'https://update.phimtop1.asia/check') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-blue-500/50 outline-none text-sm transition-all" placeholder="https://raw.githubusercontent.com/.../update.json">
                        <p class="text-xs text-gray-500 mt-2">Hệ thống sẽ tải danh sách file cần cập nhật từ URL này.</p>
                    </div>
                    <button type="submit" class="w-full bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 font-semibold py-2.5 rounded-xl border border-blue-500/30 transition-colors flex items-center justify-center gap-2 text-sm">
                        <i data-lucide="save" class="w-4 h-4"></i> Lưu Nguồn
                    </button>
                </form>
            </div>

            <!-- Manual File Upload -->
            <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-orange-500/10 rounded-full blur-[40px] pointer-events-none"></div>
                <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-800 pb-3 flex items-center gap-2">
                    <i data-lucide="upload-cloud" class="w-5 h-5 text-orange-500"></i> Cập Nhật Thủ Công
                </h3>
                <form id="manual-update-form" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Đường dẫn đích (ví dụ: <code>themes/netflix/index.php</code>)</label>
                        <input type="text" id="manual-path" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-orange-500/50 outline-none text-sm font-mono transition-all" placeholder="path/to/file.php">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Chọn file thay thế</label>
                        <input type="file" id="manual-file" required class="w-full text-sm text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gray-800 file:text-white hover:file:bg-gray-700 cursor-pointer border border-gray-700 rounded-xl p-1 bg-gray-900">
                    </div>
                    <button type="submit" id="btn-manual-update" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2 text-sm shadow-lg shadow-orange-500/20">
                        <i data-lucide="hard-drive-upload" class="w-4 h-4"></i> Ghi Đè File
                    </button>
                    <div id="manual-result" class="text-sm mt-3 hidden rounded-xl p-3 border"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="bg-gradient-to-r from-blue-900/20 to-purple-900/20 p-8 rounded-3xl border border-blue-500/20 shadow-xl relative overflow-hidden backdrop-blur-sm">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-500/10 rounded-full blur-[60px] pointer-events-none"></div>
        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <i data-lucide="shield-alert" class="w-5 h-5 text-blue-400"></i>
            </div>
            Lưu Ý Trước Khi Cập Nhật
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-900/50 rounded-2xl p-5 border border-gray-800">
                <i data-lucide="download-cloud" class="w-6 h-6 text-blue-400 mb-3"></i>
                <h4 class="font-bold text-gray-200 mb-2">Tải Từng File (No ZIP)</h4>
                <p class="text-sm text-gray-400 leading-relaxed">Hệ thống sẽ kiểm tra danh sách file bị thay đổi từ nguồn cấp và chỉ tải về, ghi đè những file đó thay vì toàn bộ mã nguồn.</p>
            </div>
            <div class="bg-gray-900/50 rounded-2xl p-5 border border-gray-800">
                <i data-lucide="database" class="w-6 h-6 text-green-400 mb-3"></i>
                <h4 class="font-bold text-gray-200 mb-2">An Toàn Dữ Liệu</h4>
                <p class="text-sm text-gray-400 leading-relaxed">CSDL và file cấu hình luôn được bảo vệ tuyệt đối. Bản cập nhật sẽ không chạm tới thư mục dữ liệu gốc.</p>
            </div>
            <div class="bg-gray-900/50 rounded-2xl p-5 border border-gray-800">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-400 mb-3"></i>
                <h4 class="font-bold text-gray-200 mb-2">Mất Code Tùy Biến</h4>
                <p class="text-sm text-gray-400 leading-relaxed">Nếu file cập nhật trùng với file bạn đã sửa trực tiếp, bản cập nhật sẽ đè lên. Luôn tạo Backup trước khi tiến hành.</p>
            </div>
        </div>
    </div>
</div>

<script src="/admin/assets/js/update.js?v=<?= time() ?>"></script>
