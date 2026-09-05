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
    <!-- Share Modal -->
    <div id="shareModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeShareModal()"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-[#141414] border border-gray-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="shareModalContent">
            <!-- Header -->
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i data-lucide="share-2" class="w-5 h-5 mr-2 text-phim-yellow"></i> Chia sẻ phim
                </h3>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 rounded-full p-2 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Movie Info Preview -->
            <div class="bg-[#1a1a1a] rounded-xl p-4 mb-5 border border-gray-800 flex items-center gap-4">
                <div class="flex-1">
                    <p class="text-sm text-gray-400 mb-1">Đang chia sẻ:</p>
                    <p id="shareMovieName" class="text-white font-bold line-clamp-2"></p>
                </div>
            </div>

            <!-- Social Share Buttons -->
            <div class="grid grid-cols-3 gap-3 mb-5">
                <button onclick="shareSocial('facebook')" class="flex flex-col items-center justify-center py-3 bg-[#1877F2]/10 hover:bg-[#1877F2]/20 border border-[#1877F2]/30 rounded-xl transition-colors group">
                    <i data-lucide="facebook" class="w-6 h-6 text-[#1877F2] mb-2 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs text-gray-300 font-medium">Facebook</span>
                </button>
                <button onclick="shareSocial('twitter')" class="flex flex-col items-center justify-center py-3 bg-[#1DA1F2]/10 hover:bg-[#1DA1F2]/20 border border-[#1DA1F2]/30 rounded-xl transition-colors group">
                    <i data-lucide="twitter" class="w-6 h-6 text-[#1DA1F2] mb-2 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs text-gray-300 font-medium">Twitter</span>
                </button>
                <button onclick="shareSocial('telegram')" class="flex flex-col items-center justify-center py-3 bg-[#0088cc]/10 hover:bg-[#0088cc]/20 border border-[#0088cc]/30 rounded-xl transition-colors group">
                    <i data-lucide="send" class="w-6 h-6 text-[#0088cc] mb-2 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs text-gray-300 font-medium">Telegram</span>
                </button>
            </div>
            
            <!-- Copy Link -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-400 mb-2">Hoặc sao chép liên kết:</label>
                <div class="flex relative">
                    <input type="text" id="shareMovieUrl" readonly class="w-full bg-[#1a1a1a] border border-gray-700 rounded-l-lg py-3 px-4 text-gray-300 text-sm focus:outline-none focus:border-phim-yellow">
                    <button onclick="copyShareLink()" class="bg-phim-yellow hover:bg-yellow-500 text-black font-bold px-5 rounded-r-lg flex items-center transition-colors">
                        <i data-lucide="copy" class="w-4 h-4 mr-2"></i> Copy
                    </button>
                </div>
                <p id="copySuccessMsg" class="text-green-500 text-xs mt-2 hidden flex items-center">
                    <i data-lucide="check-circle-2" class="w-3 h-3 mr-1"></i> Đã sao chép liên kết!
                </p>
            </div>

            <!-- App Promo -->
            <?php if (!empty($settings['appDownloadUrl'])): ?>
            <div class="bg-gradient-to-r from-gray-900 to-black p-4 rounded-xl border border-gray-800 flex items-center justify-between">
                <div>
                    <p class="text-sm text-white font-bold mb-0.5">Trải nghiệm tốt hơn</p>
                    <p class="text-xs text-gray-500">Tải App để xem phim mượt mà, không quảng cáo</p>
                </div>
                <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="shrink-0 bg-white text-black text-xs font-bold px-3 py-1.5 rounded-full hover:bg-gray-200 transition-colors">
                    Tải App
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
      let currentShareUrl = '';
      
      function shareMovie(movieName) {
          const modal = document.getElementById('shareModal');
          const content = document.getElementById('shareModalContent');
          
          // Try native Web Share API first on mobile
          if (navigator.share && /Mobi|Android/i.test(navigator.userAgent)) {
              var appUrl = '<?= !empty($settings['appDownloadUrl']) ? addslashes($settings['appDownloadUrl']) : 'https://phimtop1.com' ?>';
              var shareText = `Đang xem phim: ${movieName}\nTải app Android để xem phim mượt mà, không quảng cáo: ${appUrl}`;
              navigator.share({
                  title: movieName,
                  text: shareText,
                  url: window.location.href
              }).catch(console.error);
              return;
          }

          // Fallback to custom Modal on Desktop
          currentShareUrl = window.location.href;
          document.getElementById('shareMovieName').textContent = movieName;
          document.getElementById('shareMovieUrl').value = currentShareUrl;
          document.getElementById('copySuccessMsg').classList.add('hidden');
          
          modal.classList.remove('hidden');
          modal.classList.add('flex');
          // Trigger animation
          setTimeout(() => {
              content.classList.remove('scale-95', 'opacity-0');
              content.classList.add('scale-100', 'opacity-100');
          }, 10);
          
          if (typeof lucide !== 'undefined') lucide.createIcons();
      }

      function closeShareModal() {
          const modal = document.getElementById('shareModal');
          const content = document.getElementById('shareModalContent');
          
          content.classList.remove('scale-100', 'opacity-100');
          content.classList.add('scale-95', 'opacity-0');
          
          setTimeout(() => {
              modal.classList.remove('flex');
              modal.classList.add('hidden');
          }, 300);
      }

      function copyShareLink() {
          const urlInput = document.getElementById('shareMovieUrl');
          urlInput.select();
          urlInput.setSelectionRange(0, 99999); 
          
          if (navigator.clipboard) {
              navigator.clipboard.writeText(urlInput.value).then(() => {
                  document.getElementById('copySuccessMsg').classList.remove('hidden');
              });
          } else {
              document.execCommand('copy');
              document.getElementById('copySuccessMsg').classList.remove('hidden');
          }
      }

      function shareSocial(platform) {
          const url = encodeURIComponent(currentShareUrl);
          const title = encodeURIComponent(document.getElementById('shareMovieName').textContent);
          let shareUrl = '';
          
          switch(platform) {
              case 'facebook': shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`; break;
              case 'twitter': shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`; break;
              case 'telegram': shareUrl = `https://t.me/share/url?url=${url}&text=${title}`; break;
          }
          
          if (shareUrl) {
              window.open(shareUrl, 'share_window', 'width=600,height=500,location=no,menubar=no,toolbar=no');
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
