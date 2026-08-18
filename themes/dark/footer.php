    </div> <!-- End pt-20 -->
    
    <!-- App Download Banner -->
    <?php if (!empty($settings['appDownloadUrl']) || !empty($settings['appDownloadUrlTv'])): ?>
    <section class="mt-8 py-8 relative overflow-hidden bg-gradient-to-r from-blue-900/20 to-[#0f172a] border-y border-blue-500/20">
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-blue-500/20 via-transparent to-transparent"></div>
        <div class="container mx-auto px-4 relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="w-full md:w-2/3 text-center md:text-left">
                <span class="inline-block py-1 px-3 rounded-full bg-blue-500/20 text-blue-400 text-xs font-bold tracking-wider mb-3 border border-blue-500/30 uppercase">Trải Nghiệm Tốt Hơn</span>
                <h2 class="text-2xl md:text-3xl font-black text-white mb-3 leading-tight">Tải Ứng Dụng <span class="bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent"><?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?></span> Ngay!</h2>
                <p class="text-gray-400 text-base md:text-lg mb-6 max-w-xl mx-auto md:mx-0 leading-relaxed">Xem phim mượt mà, tải offline, không quảng cáo và nhận thông báo tập mới hoàn toàn miễn phí.</p>
                <div class="flex flex-col sm:flex-row justify-center md:justify-start gap-3">
                    <?php if (!empty($settings['appDownloadUrl'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="flex items-center justify-center bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-5 rounded-xl transition-all duration-300 shadow-[0_4px_10px_rgba(37,99,235,0.2)]">
                        <i data-lucide="smartphone" class="w-5 h-5 mr-2"></i> App Điện Thoại
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['appDownloadUrlTv'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrlTv']) ?>" target="_blank" class="flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 px-5 rounded-xl transition-all duration-300 border border-gray-700 hover:border-blue-500/50">
                        <i data-lucide="tv" class="w-5 h-5 mr-2"></i> App Smart TV
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hidden md:flex w-1/3 justify-end">
                <div class="relative group">
                    <div class="absolute inset-0 bg-blue-500/20 blur-[40px] rounded-full"></div>
                    <div class="relative z-10 w-32 h-32 lg:w-40 lg:h-40 rounded-[32px] bg-gradient-to-br from-blue-500 to-purple-600 p-1 shadow-xl rotate-[-5deg] group-hover:rotate-0 transition-transform duration-500">
                        <div class="w-full h-full rounded-[28px] bg-[#1e293b] flex items-center justify-center border border-white/10">
                            <i data-lucide="monitor-play" class="w-16 h-16 text-blue-400 opacity-90"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

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

    <!-- Mobile Floating Download Button -->
    <?php if (!empty($settings['appDownloadUrl'])): ?>
    <div class="md:hidden fixed bottom-6 right-5 z-[90]">
        <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="flex items-center justify-center w-12 h-12 bg-blue-600/90 backdrop-blur-md text-white rounded-full shadow-lg shadow-blue-500/30 border border-blue-400/50 hover:bg-blue-500 hover:scale-110 active:scale-95 transition-all duration-300 group" aria-label="Tải Ứng Dụng">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:-translate-y-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" x2="12" y1="15" y2="3"/>
            </svg>
        </a>
    </div>
    <?php endif; ?>
</body>
</html>
