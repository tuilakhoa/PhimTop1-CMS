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
                    <span class="text-xl font-bold text-[#ff8f00]"><?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?></span>
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
    
    <!-- Initialize Lucide Icons & Global Scripts -->
    <script>
      function shareMovie(movieName) {
          const movieUrl = window.location.href;
          const appUrl = '<?= !empty($settings['appDownloadUrl']) ? addslashes($settings['appDownloadUrl']) : 'https://phimtop1.com' ?>';
          const shareText = `Đang xem phim: ${movieName}\nTải app Android để xem phim mượt mà, không quảng cáo: ${appUrl}`;
          const fallbackText = `Đang xem phim: ${movieName}\nXem ngay tại: ${movieUrl}\n\nTải app Android để xem phim mượt mà, không quảng cáo: ${appUrl}`;
          
          if (navigator.share) {
              navigator.share({
                  title: movieName,
                  text: shareText,
                  url: movieUrl
              }).catch((error) => console.log('Error sharing', error));
          } else {
              if (navigator.clipboard && window.isSecureContext) {
                  navigator.clipboard.writeText(fallbackText).then(() => {
                      alert('Đã copy nội dung chia sẻ vào khay nhớ tạm!');
                  }).catch(() => {
                      prompt('Copy nội dung này để chia sẻ:', fallbackText);
                  });
              } else {
                  prompt('Copy nội dung này để chia sẻ:', fallbackText);
              }
          }
      }
      lucide.createIcons();
    </script>

    <?php if (!empty($settings['customBody'])): ?>
        <?= $settings['customBody'] ?>
    <?php endif; ?>
    <?php do_action('cms_footer'); ?>



</body>
</html>
