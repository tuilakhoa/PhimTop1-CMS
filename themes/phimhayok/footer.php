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
                    <svg class="w-6 h-6 text-[#1877F2] mb-2 group-hover:scale-110 transition-transform fill-current" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="text-xs text-gray-300 font-medium">Facebook</span>
                </button>
                <button onclick="shareSocial('x')" class="flex flex-col items-center justify-center py-3 bg-white/5 hover:bg-white/10 border border-white/20 rounded-xl transition-colors group">
                    <svg class="w-5 h-5 text-white mb-2 group-hover:scale-110 transition-transform fill-current" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    <span class="text-xs text-gray-300 font-medium">X</span>
                </button>
                <button onclick="shareSocial('telegram')" class="flex flex-col items-center justify-center py-3 bg-[#0088cc]/10 hover:bg-[#0088cc]/20 border border-[#0088cc]/30 rounded-xl transition-colors group">
                    <svg class="w-6 h-6 text-[#0088cc] mb-2 group-hover:scale-110 transition-transform fill-current" viewBox="0 0 24 24">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.888-.662 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                    <span class="text-xs text-gray-300 font-medium">Telegram</span>
                </button>
            </div>
            
            <!-- Copy Link -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-400 mb-2">Hoặc sao chép liên kết:</label>
                <div class="flex relative">
                    <input type="text" id="shareMovieUrl" readonly class="w-full bg-[#1a1a1a] border border-gray-700 rounded-l-lg py-3 px-4 text-gray-300 text-sm focus:outline-none focus:border-phim-yellow">
                    <button onclick="copyShareLink()" class="bg-phim-yellow hover:bg-yellow-500 text-black font-bold px-5 rounded-r-lg flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg> Copy
                    </button>
                </div>
                <p id="copySuccessMsg" class="text-green-500 text-xs mt-2 hidden flex items-center">
                    <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Đã sao chép liên kết!
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
              case 'x': shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`; break;
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
