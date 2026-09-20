<?php include __DIR__ . '/header.php'; ?>

<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1200px] py-12 pt-24 min-h-screen">
    <div class="mb-10 text-center">
        <h1 class="text-3xl md:text-5xl font-black text-white mb-4 uppercase tracking-wider drop-shadow-lg">
            <span class="text-red-500">Danh Sách</span> Nghệ Sĩ Vi Phạm Chủ Quyền
        </h1>
        <p class="text-gray-400 text-sm md:text-base max-w-3xl mx-auto leading-relaxed">
            Nơi tổng hợp danh sách các nghệ sĩ từng chia sẻ hoặc ủng hộ "Đường lưỡi bò". Dự án cộng đồng nhằm giúp người xem phim nhận diện và giữ vững lập trường: 
            <span class="text-red-400 font-bold">Quốc gia là trên hết, tuyệt đối không thỏa hiệp!</span>
        </p>
    </div>

    <?= $message ?? '' ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Báo Cáo -->
        <div class="lg:col-span-1">
            <div class="bg-[#141414] border border-gray-800 rounded-2xl p-6 sticky top-24 shadow-2xl">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center border-b border-gray-800 pb-4">
                    <i data-lucide="flag" class="w-5 h-5 mr-2 text-red-500"></i> Báo Cáo Nghệ Sĩ
                </h3>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-medium mb-2">Tên Diễn Viên / Nghệ Sĩ <span class="text-red-500">*</span></label>
                        <input type="text" name="actor_name" required placeholder="VD: Dương Dương, Triệu Lệ Dĩnh..." class="w-full bg-black border border-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-medium mb-2">Bằng Chứng Cụ Thể <span class="text-red-500">*</span></label>
                        <textarea name="evidence_text" required rows="3" placeholder="Mô tả bằng chứng (VD: Share bài Weibo ngày 12/7/2016...)" class="w-full bg-black border border-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-red-500 transition-colors"></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-400 text-sm font-medium mb-2">Đường Link Hình Ảnh/Bài Báo (Tùy chọn)</label>
                        <input type="url" name="evidence_url" placeholder="https://..." class="w-full bg-black border border-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                    
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i> Gửi Báo Cáo
                    </button>
                    <p class="text-gray-500 text-xs mt-3 text-center italic">Thông tin sẽ được cộng đồng kiểm duyệt trước khi hiển thị công khai.</p>
                </form>
            </div>
        </div>

        <!-- Danh sách -->
        <div class="lg:col-span-2">
            <div class="bg-[#141414] border border-gray-800 rounded-2xl p-6 shadow-2xl">
                <div class="flex flex-col md:flex-row items-center justify-between mb-6 border-b border-gray-800 pb-4 gap-4">
                    <h3 class="text-xl font-bold text-white flex items-center whitespace-nowrap">
                        <i data-lucide="list" class="w-5 h-5 mr-2 text-phim-yellow"></i> Danh Sách Tổng Hợp
                    </h3>
                    
                    <form method="GET" action="" class="w-full md:w-auto flex-1 md:max-w-xs flex gap-2">
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Tìm diễn viên..." class="w-full bg-black border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500 transition-colors">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-3 py-2 rounded-lg transition-colors">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </form>
                    
                    <span class="text-gray-500 text-sm bg-black px-3 py-1 rounded-full border border-gray-800 whitespace-nowrap hidden md:inline-block">
                        <?= $total_reports ?? count($approved_reports ?? []) ?> kết quả
                    </span>
                </div>
                
                <?php if (empty($approved_reports)): ?>
                    <div class="text-center py-12 text-gray-500">
                        <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                        <p>Không tìm thấy nghệ sĩ nào phù hợp.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($approved_reports as $report): ?>
                            <div class="bg-black border border-gray-800 hover:border-red-900/50 rounded-xl p-5 transition-colors relative overflow-hidden group">
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gray-800 group-hover:bg-red-600 transition-colors"></div>
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-lg font-bold text-white flex items-center">
                                                <i data-lucide="user-x" class="w-4 h-4 mr-2 text-red-500"></i> <?= htmlspecialchars($report['actor_name']) ?>
                                            </h4>
                                            <?php if($report['status'] === 'pending'): ?>
                                                <span class="text-[10px] bg-yellow-900/50 text-yellow-500 px-2 py-0.5 rounded border border-yellow-700/50 uppercase font-bold tracking-wider">Chờ duyệt</span>
                                            <?php else: ?>
                                                <span class="text-[10px] bg-red-900/50 text-red-400 px-2 py-0.5 rounded border border-red-700/50 uppercase font-bold tracking-wider">Đã xác minh</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-gray-400 text-sm bg-gray-900/50 p-3 rounded-lg border border-gray-800/50 mb-3">
                                            <span class="text-gray-500 text-xs uppercase font-bold block mb-1">Nội dung vi phạm:</span>
                                            <?= nl2br(htmlspecialchars($report['evidence_text'])) ?>
                                        </div>
                                        <?php if (!empty($report['evidence_url'])): ?>
                                            <a href="<?= htmlspecialchars($report['evidence_url']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-xs text-blue-400 hover:text-blue-300 bg-blue-900/20 px-3 py-1.5 rounded-full border border-blue-800/30 transition-colors">
                                                <i data-lucide="external-link" class="w-3 h-3 mr-1.5"></i> Xem bằng chứng
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Phân Trang -->
                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <div class="mt-8 flex justify-center items-center space-x-2">
                        <?php 
                        $search_query = !empty($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '';
                        
                        if ($page > 1): ?>
                            <a href="?p=<?= $page - 1 ?><?= $search_query ?>" class="px-3 py-2 bg-gray-900 border border-gray-800 hover:border-red-500 rounded text-gray-400 hover:text-white transition-colors">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                        
                        <span class="px-4 py-2 text-sm text-gray-400">
                            Trang <?= $page ?> / <?= $total_pages ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?p=<?= $page + 1 ?><?= $search_query ?>" class="px-3 py-2 bg-gray-900 border border-gray-800 hover:border-red-500 rounded text-gray-400 hover:text-white transition-colors">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
