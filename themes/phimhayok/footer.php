    </main>

    <!-- App Download Banner -->
    <?php if (!empty($settings['appDownloadUrl']) || !empty($settings['appDownloadUrlTv'])): ?>
    <section class="mt-8 py-8 relative overflow-hidden bg-gradient-to-r from-yellow-600/10 to-[#0f0f0f] border-y border-yellow-500/20">
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-yellow-500/20 via-transparent to-transparent"></div>
        <div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1400px] relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="w-full md:w-2/3 text-center md:text-left">
                <span class="inline-block py-1 px-3 rounded-full bg-yellow-500/20 text-yellow-500 text-xs font-bold tracking-wider mb-3 border border-yellow-500/30 uppercase">Trải Nghiệm Tốt Hơn</span>
                <h2 class="text-2xl md:text-3xl font-black text-white mb-3 leading-tight">Tải Ứng Dụng <span class="text-yellow-500"><?= htmlspecialchars($settings['siteName'] ?? 'PhimHayOK') ?></span> Ngay!</h2>
                <p class="text-gray-400 text-base md:text-lg mb-6 max-w-xl mx-auto md:mx-0 leading-relaxed">Xem phim mượt mà, tải offline, không quảng cáo và nhận thông báo tập mới hoàn toàn miễn phí.</p>
                <div class="flex flex-col sm:flex-row justify-center md:justify-start gap-3">
                    <?php if (!empty($settings['appDownloadUrl'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="flex items-center justify-center bg-yellow-500 hover:bg-yellow-400 text-black font-bold py-3 px-5 rounded-xl transition-all duration-300 shadow-[0_4px_10px_rgba(234,179,8,0.2)]">
                        <i data-lucide="smartphone" class="w-5 h-5 mr-2"></i> App Điện Thoại
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['appDownloadUrlTv'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrlTv']) ?>" target="_blank" class="flex items-center justify-center bg-[#1a1a1a] hover:bg-[#252525] text-white font-bold py-3 px-5 rounded-xl transition-all duration-300 border border-white/10 hover:border-yellow-500/50">
                        <i data-lucide="tv" class="w-5 h-5 mr-2"></i> App Smart TV
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hidden md:flex w-1/3 justify-end">
                <div class="relative group">
                    <div class="absolute inset-0 bg-yellow-500/20 blur-[40px] rounded-full"></div>
                    <div class="relative z-10 w-32 h-32 lg:w-40 lg:h-40 rounded-[32px] bg-gradient-to-br from-yellow-400 to-yellow-600 p-1 shadow-xl rotate-[-5deg] group-hover:rotate-0 transition-transform duration-500">
                        <div class="w-full h-full rounded-[28px] bg-[#111] flex items-center justify-center border border-white/10">
                            <i data-lucide="monitor-play" class="w-16 h-16 text-yellow-500 opacity-90"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer class="bg-[#0a0a0a] border-t border-gray-900 py-12">
        <div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1400px]">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-1 md:col-span-2">
                    <a href="/" class="flex items-center space-x-2 mb-4">
                        <?php if (!empty($settings['logoUrl'])): ?>
                            <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" class="h-8 object-contain opacity-80 hover:opacity-100 transition-opacity">
                        <?php else: ?>
                            <div class="text-2xl font-black tracking-tighter text-red-600 uppercase">
                                PhimHayOK
                            </div>
                        <?php endif; ?>
                    </a>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-md mb-4">
                        <?= nl2br(htmlspecialchars($settings['footerText'] ?: 'PhimHayOK - Nền tảng xem phim online miễn phí chất lượng cao 4K/Full HD. Kho phim khổng lồ đa dạng thể loại, cập nhật nhanh nhất các bộ phim hot, phim chiếu rạp. Trải nghiệm mượt mà, không chứa quảng cáo.')) ?>
                    </p>
                    <div class="flex items-center space-x-4 mb-4">
                        <?php if (!empty($settings['socialFacebook'])): ?><a href="<?= htmlspecialchars($settings['socialFacebook']) ?>" target="_blank" class="text-gray-500 hover:text-blue-500 transition-colors"><i data-lucide="facebook" class="w-5 h-5"></i></a><?php endif; ?>
                        <?php if (!empty($settings['socialYoutube'])): ?><a href="<?= htmlspecialchars($settings['socialYoutube']) ?>" target="_blank" class="text-gray-500 hover:text-red-500 transition-colors"><i data-lucide="youtube" class="w-5 h-5"></i></a><?php endif; ?>
                        <?php if (!empty($settings['socialTwitter'])): ?><a href="<?= htmlspecialchars($settings['socialTwitter']) ?>" target="_blank" class="text-gray-500 hover:text-blue-400 transition-colors"><i data-lucide="twitter" class="w-5 h-5"></i></a><?php endif; ?>
                        <?php if (!empty($settings['socialTelegram'])): ?><a href="<?= htmlspecialchars($settings['socialTelegram']) ?>" target="_blank" class="text-gray-500 hover:text-blue-400 transition-colors"><i data-lucide="send" class="w-5 h-5"></i></a><?php endif; ?>
                    </div>
                    <?php if (!empty($settings['customFooter'])): ?>
                        <div class="mt-4 text-gray-500 text-sm">
                            <?= $settings['customFooter'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Khám Phá</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-moi" class="hover:text-red-500 transition-colors">Phim Mới</a></li>
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="hover:text-red-500 transition-colors">Phim Lẻ</a></li>
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo" class="hover:text-red-500 transition-colors">Phim Bộ</a></li>
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-chieu-rap" class="hover:text-red-500 transition-colors">Phim Chiếu Rạp</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Hỗ Trợ</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="hover:text-white transition-colors">Giới thiệu</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Liên hệ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Điều khoản dịch vụ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Chính sách bảo mật</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Khiếu nại bản quyền</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-900 pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-gray-600">
                <p>&copy; <?= date('Y') ?> PhimHayOK. All rights reserved.</p>
                <div class="mt-4 md:mt-0 flex space-x-4">
                    <span class="hover:text-gray-300 cursor-pointer">Hoàng Sa & Trường Sa là của Việt Nam!</span>
                </div>
            </div>
        </div>
    </footer>
    <script>
        lucide.createIcons();
    </script>
    <?php do_action('cms_footer'); ?>

    <!-- Mobile Floating Download Button -->
    <?php if (!empty($settings['appDownloadUrl'])): ?>
    <div class="md:hidden fixed bottom-6 right-5 z-[90]">
        <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="flex items-center justify-center w-12 h-12 bg-yellow-500/90 backdrop-blur-md text-black rounded-full shadow-lg shadow-yellow-500/20 border border-yellow-400/50 hover:bg-yellow-400 hover:scale-110 active:scale-95 transition-all duration-300 group" aria-label="Tải Ứng Dụng">
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
