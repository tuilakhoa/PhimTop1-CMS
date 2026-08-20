    </div> <!-- End pt-20 -->
    


    <footer class="bg-gray-900 border-t border-gray-800 text-gray-400 py-12 mt-auto">
        <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <a href="/" class="flex items-center space-x-2 mb-4 hover:opacity-80 transition-opacity">
                    <?php if (!empty($settings['logoUrl'])): ?>
                        <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" class="w-8 h-8 object-contain">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                            <i data-lucide="monitor-play" class="w-5 h-5 text-white fill-current"></i>
                        </div>
                    <?php endif; ?>
                    <span class="text-xl font-bold bg-gradient-to-r from-blue-500 to-purple-500 bg-clip-text text-transparent"><?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?></span>
                </a>
                <p class="text-sm mb-4"><?= nl2br(htmlspecialchars($settings['footerText'] ?: ($settings['seoDesc'] ?? 'Hệ thống xem phim trực tuyến chất lượng cao, cập nhật liên tục mỗi ngày.'))) ?></p>
                <div class="flex items-center space-x-4">
                    <?php if (!empty($settings['socialFacebook'])): ?><a href="<?= htmlspecialchars($settings['socialFacebook']) ?>" target="_blank" class="text-gray-400 hover:text-blue-600 transition-colors"><i data-lucide="facebook" class="w-5 h-5"></i></a><?php endif; ?>
                    <?php if (!empty($settings['socialYoutube'])): ?><a href="<?= htmlspecialchars($settings['socialYoutube']) ?>" target="_blank" class="text-gray-400 hover:text-red-600 transition-colors"><i data-lucide="youtube" class="w-5 h-5"></i></a><?php endif; ?>
                    <?php if (!empty($settings['socialTwitter'])): ?><a href="<?= htmlspecialchars($settings['socialTwitter']) ?>" target="_blank" class="text-gray-400 hover:text-blue-500 transition-colors"><i data-lucide="twitter" class="w-5 h-5"></i></a><?php endif; ?>
                    <?php if (!empty($settings['socialTelegram'])): ?><a href="<?= htmlspecialchars($settings['socialTelegram']) ?>" target="_blank" class="text-gray-400 hover:text-blue-500 transition-colors"><i data-lucide="send" class="w-5 h-5"></i></a><?php endif; ?>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-semibold mb-4">Thể Loại</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/hanh-dong" class="hover:text-red-500 transition-colors">Hành Động</a></li>
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/tinh-cam" class="hover:text-red-500 transition-colors">Tình Cảm</a></li>
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/hai-huoc" class="hover:text-red-500 transition-colors">Hài Hước</a></li>
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/kinh-di" class="hover:text-red-500 transition-colors">Kinh Dị</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-white font-semibold mb-4">Quốc Gia</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/phim-viet-nam" class="hover:text-red-500 transition-colors">Việt Nam</a></li>
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/phim-han-quoc" class="hover:text-red-500 transition-colors">Hàn Quốc</a></li>
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/phim-trung-quoc" class="hover:text-red-500 transition-colors">Trung Quốc</a></li>
                    <li><a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/phim-au-my" class="hover:text-red-500 transition-colors">Âu Mỹ</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-white font-semibold mb-4">Liên Kết</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-red-500 transition-colors">Giới Thiệu</a></li>
                    <li><a href="#" class="hover:text-red-500 transition-colors">Điều Khoản</a></li>
                    <li><a href="#" class="hover:text-red-500 transition-colors">Bản Quyền</a></li>
                    <li><a href="#" class="hover:text-red-500 transition-colors">Liên Hệ</a></li>
                    <?php if (!empty($settings['appDownloadUrl'])): ?>
                    <li><a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="hover:text-red-500 transition-colors">Tải Ứng Dụng</a></li>
                    <?php endif; ?>
                    <?php if (!empty($settings['appDownloadUrlTv'])): ?>
                    <li><a href="<?= htmlspecialchars($settings['appDownloadUrlTv']) ?>" target="_blank" class="hover:text-red-500 transition-colors">App TV</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="container mx-auto px-4 mt-8 pt-8 border-t border-gray-800 text-sm text-center">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?> CMS - Powered by PHP. All rights reserved.
        </div>
    </footer>
    
    <!-- Initialize Lucide Icons -->
    <script>
      lucide.createIcons();
    </script>

    <?php if (!empty($settings['customBody'])): ?>
        <?= $settings['customBody'] ?>
    <?php endif; ?>
    <?php do_action('cms_footer'); ?>

    <!-- App Promo Popup -->
    <?php if (!empty($settings['appDownloadUrl'])): ?>
    <div id="appPromoPopup" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/80" onclick="closeAppPromo()"></div>
        <div class="relative bg-gray-900 border border-gray-700 w-[90%] max-w-sm rounded-2xl shadow-xl overflow-hidden" id="appPromoModal">
            <button onclick="closeAppPromo()" class="absolute top-3 right-3 text-gray-400 hover:text-white bg-gray-800/50 hover:bg-gray-700 p-1.5 rounded-full z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <div class="p-6 text-center">
                <?php if (!empty($settings['logoUrl'])): ?>
                    <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" class="w-16 h-16 mx-auto mb-4 object-contain">
                <?php else: ?>
                    <div class="w-16 h-16 bg-blue-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                        <i data-lucide="monitor-play" class="w-8 h-8 text-white"></i>
                    </div>
                <?php endif; ?>
                <h3 class="text-xl font-bold text-white mb-2">Trải Nghiệm Tốt Hơn</h3>
                <p class="text-sm text-gray-400 mb-6">Tải ngay ứng dụng <strong><?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?></strong> để xem phim mượt mà hơn và không quảng cáo!</p>
                <div class="space-y-3">
                    <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" onclick="closeAppPromo()" class="flex items-center justify-center w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-4 rounded-xl">
                        <i data-lucide="download" class="w-5 h-5 mr-2"></i> Tải App Ngay
                    </a>
                    <button onclick="closeAppPromo()" class="w-full text-gray-400 hover:text-white text-sm font-medium py-2">
                        Để sau
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function closeAppPromo() {
            const popup = document.getElementById('appPromoPopup');
            if(popup) {
                popup.classList.add('hidden');
                popup.classList.remove('flex');
            }
            // Lưu trạng thái ẩn popup trong 24 giờ (86400000 milliseconds)
            const expiry = new Date().getTime() + 86400000;
            localStorage.setItem('appPromoExpiry', expiry);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const isAndroid = /Android/i.test(navigator.userAgent);
            if (!isAndroid) return;

            const expiry = localStorage.getItem('appPromoExpiry');
            const now = new Date().getTime();
            
            // Nếu chưa có lịch sử đóng hoặc đã quá 24h kể từ lần đóng trước
            if (!expiry || now > parseInt(expiry)) {
                const popup = document.getElementById('appPromoPopup');
                if (popup) {
                    setTimeout(() => {
                        popup.classList.remove('hidden');
                        popup.classList.add('flex');
                    }, 500); // Rút ngắn thời gian hiện xuống 0.5s, bỏ hiệu ứng mờ dần
                }
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>
