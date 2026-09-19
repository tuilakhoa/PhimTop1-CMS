const fs = require('fs');

const replacement = `<nav class="hidden md:flex items-center gap-6 text-sm font-bold text-gray-300 ml-4">
                        <div class="relative group">
                            <a href="#" class="hover:text-white transition py-4 flex items-center gap-1">Thể Loại <i data-lucide="chevron-down" class="w-3 h-3"></i></a>
                            <div class="absolute left-0 top-full -mt-2 w-[600px] bg-[#111319]/95 backdrop-blur-xl border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-5 grid grid-cols-4 gap-x-2 gap-y-1.5 max-h-[65vh] overflow-y-auto custom-scrollbar">
                                <% if(locals.globalGenres) { 
                                    const sorted = [...globalGenres].sort((a,b) => a.name.localeCompare(b.name, 'vi'));
                                    sorted.forEach(g => { %>
                                    <a href="/the-loai/<%= g.slug %>" class="text-gray-400 hover:text-white hover:bg-white/10 text-[13px] py-2 px-3 rounded-lg transition-all capitalize truncate font-medium"><%= g.name %></a>
                                <% }) } %>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="hover:text-white transition py-4 flex items-center gap-1">Quốc Gia <i data-lucide="chevron-down" class="w-3 h-3"></i></a>
                            <div class="absolute left-0 top-full -mt-2 w-[600px] bg-[#111319]/95 backdrop-blur-xl border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-5 grid grid-cols-4 gap-x-2 gap-y-1.5 max-h-[65vh] overflow-y-auto custom-scrollbar">
                                <% if(locals.globalCountries) { 
                                    const sorted = [...globalCountries].sort((a,b) => a.name.localeCompare(b.name, 'vi'));
                                    sorted.forEach(c => { %>
                                    <a href="/quoc-gia/<%= c.slug %>" class="text-gray-400 hover:text-white hover:bg-white/10 text-[13px] py-2 px-3 rounded-lg transition-all capitalize truncate font-medium"><%= c.name %></a>
                                <% }) } %>
                            </div>
                        </div>
                        <div class="relative group">
                            <a href="#" class="hover:text-white transition py-4 flex items-center gap-1">Danh Sách <i data-lucide="chevron-down" class="w-3 h-3"></i></a>
                            <div class="absolute left-0 top-full -mt-2 w-48 bg-[#181a20] border border-[#272a30] rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 flex flex-col p-2">
                                <a href="/danh-sach/phim-le" class="text-gray-400 hover:text-white hover:bg-gray-800 text-sm py-2 px-3 rounded transition">Phim Lẻ</a>
                                <a href="/danh-sach/phim-bo" class="text-gray-400 hover:text-white hover:bg-gray-800 text-sm py-2 px-3 rounded transition">Phim Bộ</a>
                                <a href="/danh-sach/hoat-hinh" class="text-gray-400 hover:text-white hover:bg-gray-800 text-sm py-2 px-3 rounded transition">Hoạt Hình</a>
                                <a href="/danh-sach/tvshows" class="text-gray-400 hover:text-white hover:bg-gray-800 text-sm py-2 px-3 rounded transition">TV Shows</a>
                            </div>
                        </div>
                        <a href="/cms" class="hover:text-white transition py-4 flex items-center gap-1 ml-4 border-l border-gray-800 pl-6 text-kk-blue">
                            <i data-lucide="book-open" class="w-4 h-4"></i> Tài liệu API
                        </a>
                    </nav>`;

['views/index.ejs', 'views/detail.ejs'].forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    // Using regex to replace the nav block
    content = content.replace(/<nav class="hidden md:flex items-center gap-6 text-sm font-bold text-gray-300 ml-4">[\s\S]*?<\/nav>/, replacement);
    fs.writeFileSync(file, content, 'utf8');
});

console.log("Updated EJS files!");
