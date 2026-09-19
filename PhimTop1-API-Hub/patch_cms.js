const fs = require('fs');

let content = fs.readFileSync('views/cms.ejs', 'utf-8');

// Change grid-cols-3 to grid-cols-2 for Downloads Grid
content = content.replace('<div class="grid grid-cols-1 md:grid-cols-3 gap-8">', '<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">');

// Remove Theme Mẫu
const themeMauStart = content.indexOf('<!-- Theme Mẫu -->');
const downloadsGridEnd = content.indexOf('</div>', content.indexOf('</div>', content.indexOf('</div>', themeMauStart) + 1) + 1) + 6;

const themeLibrary = `
        <!-- Theme Library Section -->
        <div class="mt-20">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Thư viện Giao diện (Themes)</h2>
                    <p class="text-gray-400">Khám phá và cài đặt hàng tá giao diện tối ưu riêng cho PhimTop1. Hỗ trợ 1-click install.</p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="Tìm kiếm theme..." class="w-full bg-card border border-card text-white text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 pl-10 p-2.5 outline-none">
                    </div>
                    <select class="bg-card border border-card text-white text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 p-2.5 outline-none whitespace-nowrap">
                        <option>Mới nhất</option>
                        <option>Phổ biến nhất</option>
                        <option>Miễn phí</option>
                        <option>Cao cấp</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Theme Card 1 -->
                <div class="bg-card border border-card rounded-xl overflow-hidden group hover:border-cyan-500/50 transition duration-300">
                    <div class="relative aspect-video bg-[#1f2937] overflow-hidden">
                        <div class="w-full h-full flex items-center justify-center text-gray-500 group-hover:scale-105 transition duration-500">
                            <i data-lucide="image" class="w-16 h-16 opacity-20"></i>
                            <span class="absolute text-xl font-bold opacity-30">DarkMovie Pro</span>
                        </div>
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4 backdrop-blur-sm">
                            <button class="bg-cyan-600 hover:bg-cyan-500 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center gap-2"><i data-lucide="eye" class="w-4 h-4"></i> Xem Trước</button>
                        </div>
                    </div>
                    <div class="p-5 flex flex-col h-[180px]">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-white">DarkMovie Pro</h3>
                            <span class="bg-green-500/10 text-green-400 text-xs px-2.5 py-1 rounded-full font-medium border border-green-500/20">Miễn phí</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2 flex-grow">Giao diện tối giản tông màu tối, tập trung vào trải nghiệm xem phim. Tốc độ load cực nhanh và tương thích hoàn hảo di động.</p>
                        <div class="flex items-center justify-between border-t border-card pt-4 mt-auto">
                            <div class="flex items-center gap-1 text-yellow-500 text-sm">
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                <span class="font-medium text-white">4.9</span>
                                <span class="text-gray-500 text-xs ml-1">(128)</span>
                            </div>
                            <button class="bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500 hover:text-white border border-cyan-500/20 font-medium px-4 py-1.5 rounded text-sm flex items-center gap-2 transition">
                                <i data-lucide="download" class="w-4 h-4"></i> Cài đặt
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Theme Card 2 -->
                <div class="bg-card border border-card rounded-xl overflow-hidden group hover:border-cyan-500/50 transition duration-300">
                    <div class="relative aspect-video bg-[#1f2937] overflow-hidden">
                        <div class="w-full h-full flex items-center justify-center text-gray-500 group-hover:scale-105 transition duration-500">
                            <i data-lucide="image" class="w-16 h-16 opacity-20"></i>
                            <span class="absolute text-xl font-bold opacity-30">AnimeChill</span>
                        </div>
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4 backdrop-blur-sm">
                            <button class="bg-cyan-600 hover:bg-cyan-500 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center gap-2"><i data-lucide="eye" class="w-4 h-4"></i> Xem Trước</button>
                        </div>
                    </div>
                    <div class="p-5 flex flex-col h-[180px]">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-white">Anime Chill Light</h3>
                            <span class="bg-green-500/10 text-green-400 text-xs px-2.5 py-1 rounded-full font-medium border border-green-500/20">Miễn phí</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2 flex-grow">Theme sáng, màu sắc tươi tắn phù hợp cho website anime hoặc phim bộ tuổi teen, tích hợp sẵn bình luận Facebook.</p>
                        <div class="flex items-center justify-between border-t border-card pt-4 mt-auto">
                            <div class="flex items-center gap-1 text-yellow-500 text-sm">
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                <span class="font-medium text-white">4.7</span>
                                <span class="text-gray-500 text-xs ml-1">(85)</span>
                            </div>
                            <button class="bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500 hover:text-white border border-cyan-500/20 font-medium px-4 py-1.5 rounded text-sm flex items-center gap-2 transition">
                                <i data-lucide="download" class="w-4 h-4"></i> Cài đặt
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Theme Card 3 -->
                <div class="bg-card border border-card rounded-xl overflow-hidden group hover:border-purple-500/50 transition duration-300">
                    <div class="relative aspect-video bg-[#1f2937] overflow-hidden">
                        <div class="w-full h-full flex items-center justify-center text-gray-500 group-hover:scale-105 transition duration-500">
                            <i data-lucide="image" class="w-16 h-16 opacity-20"></i>
                            <span class="absolute text-xl font-bold opacity-30 text-purple-400">NetFlix Clone</span>
                        </div>
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4 backdrop-blur-sm">
                            <button class="bg-cyan-600 hover:bg-cyan-500 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center gap-2"><i data-lucide="eye" class="w-4 h-4"></i> Xem Trước</button>
                        </div>
                    </div>
                    <div class="p-5 flex flex-col h-[180px]">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-white">Netflx Premium</h3>
                            <span class="bg-purple-500/10 text-purple-400 text-xs px-2.5 py-1 rounded-full font-medium border border-purple-500/20">Premium</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2 flex-grow">Giao diện chuyên nghiệp lấy cảm hứng từ nền tảng stream số 1 thế giới. Hỗ trợ trailer popup, hiệu ứng hover mượt mà.</p>
                        <div class="flex items-center justify-between border-t border-card pt-4 mt-auto">
                            <div class="flex items-center gap-1 text-yellow-500 text-sm">
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                <span class="font-medium text-white">5.0</span>
                                <span class="text-gray-500 text-xs ml-1">(210)</span>
                            </div>
                            <button class="bg-purple-600 hover:bg-purple-500 text-white font-medium px-4 py-1.5 rounded text-sm flex items-center gap-2 transition">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Mua $19
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-10 text-center">
                <button class="px-6 py-2.5 bg-card border border-card hover:border-cyan-500/50 hover:text-cyan-400 text-white rounded-lg transition font-medium">Tải thêm Themes...</button>
            </div>
        </div>
`;

content = content.substring(0, themeMauStart) + '</div>\n' + themeLibrary + content.substring(downloadsGridEnd);

fs.writeFileSync('views/cms.ejs', content);
console.log('Successfully patched cms.ejs');
