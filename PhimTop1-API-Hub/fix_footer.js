const fs = require('fs');

const newFooter = `<footer class="bg-[#0b0c0f] border-t border-card mt-auto text-gray-400">
        <div class="max-w-[1440px] mx-auto px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Về chúng tôi -->
                <div class="space-y-4">
                    <a href="/" class="flex items-center gap-2 font-black text-2xl tracking-tighter hover:opacity-80 transition inline-block">
                        <i data-lucide="clapperboard" class="w-7 h-7 text-orange-500 inline-block align-middle"></i>
                        <span class="text-orange-500">PhimTop1<span class="text-kk-blue">API</span></span>
                    </a>
                    <p class="text-sm leading-relaxed text-gray-500">
                        PhimTop1 API Hub cung cấp nền tảng dữ liệu phim định dạng JSON tốc độ cao, miễn phí và không giới hạn băng thông dành cho Developer & Webmaster.
                    </p>
                    <div class="inline-block px-4 py-2 bg-gray-800/50 rounded-lg border border-gray-700/50 mt-2 hover:bg-gray-800 transition">
                        <a href="https://phimtop1.asia/" target="_blank" class="text-sm flex items-center gap-2 font-bold text-gray-300 hover:text-white">
                            <i data-lucide="monitor-play" class="w-4 h-4 text-orange-500"></i> Xem phim trực tuyến tại PhimTop1.asia
                        </a>
                    </div>
                </div>

                <!-- Liên kết nhanh -->
                <div class="md:pl-12">
                    <h3 class="text-white font-bold mb-4 uppercase text-xs tracking-wider">Liên kết hệ thống</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="/" class="hover:text-kk-blue transition flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> Trang chủ API</a></li>
                        <li><a href="/api-document" class="hover:text-kk-blue transition flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> Tài liệu hướng dẫn</a></li>
                        <li><a href="/cms" class="hover:text-kk-blue transition flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> Mã nguồn CMS Mẫu</a></li>
                        <li><a href="https://phimtop1.asia/" target="_blank" class="hover:text-kk-blue transition flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3"></i> PhimTop1.asia</a></li>
                    </ul>
                </div>

                <!-- Hỗ trợ -->
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-xs tracking-wider">Trợ giúp & Hỗ trợ</h3>
                    <p class="text-sm text-gray-500 mb-4">Gặp lỗi hoặc cần yêu cầu thêm phim? Hãy liên hệ với chúng tôi.</p>
                    <div class="flex flex-col gap-3">
                        <a href="https://t.me/" target="_blank" class="w-fit text-sm font-bold border border-[#0088cc]/50 hover:bg-[#0088cc]/10 text-gray-300 hover:text-white px-5 py-2 rounded-lg flex items-center gap-2 transition">
                            <i data-lucide="send" class="w-4 h-4 text-[#0088cc]"></i> Cộng đồng Telegram
                        </a>
                        <button class="w-fit text-sm font-bold border border-gray-700 hover:bg-gray-800 text-gray-300 hover:text-white px-5 py-2 rounded-lg flex items-center gap-2 transition">
                            <i data-lucide="mail" class="w-4 h-4 text-orange-500"></i> Yêu cầu cập nhật phim
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-10 pt-6 border-t border-gray-800/50 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-600">
                <p>&copy; 2026 PhimTop1 API Hub. Vận hành bởi PhimTop1.asia.</p>
                <p>Designed with <i data-lucide="heart" class="w-3 h-3 inline text-red-500"></i> for Developers.</p>
            </div>
        </div>
    </footer>`;

const files = ['views/index.ejs', 'views/detail.ejs', 'views/api-document.ejs'];

files.forEach(file => {
    if (fs.existsSync(file)) {
        let content = fs.readFileSync(file, 'utf8');
        
        // Match the old footer block roughly
        // In index.ejs: <footer class="py-8 text-center text-sm text-gray-600 bg-[#0b0c0f] border-t border-card flex flex-col items-center justify-center gap-4 mt-auto"> ... </footer>
        // We will just use regex to replace everything from <footer to </footer>
        content = content.replace(/<footer[\s\S]*?<\/footer>/, newFooter);
        
        fs.writeFileSync(file, content, 'utf8');
    }
});

console.log("Updated footers.");
