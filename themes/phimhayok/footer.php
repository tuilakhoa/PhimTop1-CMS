    </main>



    <footer class="bg-[#0a0a0a] border-t border-gray-900 py-12">
        <div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1400px]">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-1 md:col-span-2">
                    <a href="/" class="flex items-center space-x-2 mb-4">
                        <?php if (!empty($settings['logoUrl'])): ?>
                            <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" class="h-8 object-contain opacity-80 hover:opacity-100 ">
                        <?php else: ?>
                            <div class="text-2xl font-black tracking-tighter text-red-600 uppercase">
                                <?= htmlspecialchars($siteName ?? "PhimTop1") ?>
                            </div>
                        <?php endif; ?>
                    </a>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-md mb-4">
                        <?= nl2br(htmlspecialchars($settings['footerText'] ?: 'Hệ thống xem phim online miễn phí chất lượng cao 4K/Full HD. Kho phim khổng lồ đa dạng thể loại, cập nhật nhanh nhất các bộ phim hot, phim chiếu rạp. Trải nghiệm mượt mà, không chứa quảng cáo.')) ?>
                    </p>
                    <div class="flex items-center space-x-4 mb-4">
                        <?php if (!empty($settings['socialFacebook'])): ?><a href="<?= htmlspecialchars($settings['socialFacebook']) ?>" target="_blank" class="text-gray-500 hover:text-blue-500 "><i data-lucide="facebook" class="w-5 h-5"></i></a><?php endif; ?>
                        <?php if (!empty($settings['socialYoutube'])): ?><a href="<?= htmlspecialchars($settings['socialYoutube']) ?>" target="_blank" class="text-gray-500 hover:text-red-500 "><i data-lucide="youtube" class="w-5 h-5"></i></a><?php endif; ?>
                        <?php if (!empty($settings['socialTwitter'])): ?><a href="<?= htmlspecialchars($settings['socialTwitter']) ?>" target="_blank" class="text-gray-500 hover:text-blue-400 "><i data-lucide="twitter" class="w-5 h-5"></i></a><?php endif; ?>
                        <?php if (!empty($settings['socialTelegram'])): ?><a href="<?= htmlspecialchars($settings['socialTelegram']) ?>" target="_blank" class="text-gray-500 hover:text-blue-400 "><i data-lucide="send" class="w-5 h-5"></i></a><?php endif; ?>
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
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-moi" class="hover:text-red-500 ">Phim Mới</a></li>
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="hover:text-red-500 ">Phim Lẻ</a></li>
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo" class="hover:text-red-500 ">Phim Bộ</a></li>
                        <li><a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-chieu-rap" class="hover:text-red-500 ">Phim Chiếu Rạp</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Hỗ Trợ</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="hover:text-white ">Giới thiệu</a></li>
                        <li><a href="#" class="hover:text-white ">Liên hệ</a></li>
                        <li><a href="#" class="hover:text-white ">Điều khoản dịch vụ</a></li>
                        <li><a href="#" class="hover:text-white ">Chính sách bảo mật</a></li>
                        <li><a href="#" class="hover:text-white ">Khiếu nại bản quyền</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-900 pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-gray-600">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName ?? "PhimTop1") ?>. All rights reserved.</p>
                <div class="mt-4 md:mt-0 flex space-x-4">
                    <span class="text-red-500 font-bold hover:text-red-400 text-sm uppercase tracking-wide cursor-pointer transition-colors duration-300 drop-shadow-md">Hoàng Sa & Trường Sa là của Việt Nam! 🇻🇳</span>
                </div>
            </div>
        </div>
    </footer>
    <script>
      function shareMovie(movieName) {
          var movieUrl = window.location.href;
          var appUrl = '<?= !empty($settings['appDownloadUrl']) ? addslashes($settings['appDownloadUrl']) : 'https://phimtop1.com' ?>';
          var shareText = `Đang xem phim: ${movieName}\nTải app Android để xem phim mượt mà, không quảng cáo: ${appUrl}`;
          var fallbackText = `Đang xem phim: ${movieName}\nXem ngay tại: ${movieUrl}\n\nTải app Android để xem phim mượt mà, không quảng cáo: ${appUrl}`;
          
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
      if (typeof lucide !== 'undefined') {
          lucide.createIcons();
      } else {
          document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
      }
    </script>
    <?php do_action('cms_footer'); ?>


</body>
</html>
