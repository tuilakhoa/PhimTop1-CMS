import re

file_path = 'themes/phimhayok/assets/js/home.js'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

def replace_between(text, start_str, end_str, new_content):
    start = text.find(start_str)
    if start == -1: return text
    end = text.find(end_str, start)
    if end == -1: return text
    return text[:start] + new_content + text[end + len(end_str):]

new_ai_html = """let html = '';
                        res.data.forEach(item => {
                            let thumb = item.poster_url || item.thumb_url;
                            if (thumb && !thumb.startsWith('http')) {
                                thumb = 'https://phimimg.com/' + thumb;
                            }
                            html += `
                                <a href="/phim/${item.slug}" class="swiper-slide group block w-32 sm:w-40 md:w-48 lg:w-56 shrink-0 relative transition-transform duration-500 hover:scale-105 hover:z-50 cursor-pointer">
                                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-gray-900 shadow-lg group-hover:shadow-2xl group-hover:shadow-phim-yellow/20 transition-all duration-500">
                                        <img src="${thumb}" alt="${item.name}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        
                                        <!-- Badge Gợi ý -->
                                        <div class="absolute top-2 left-2 bg-phim-yellow/90 backdrop-blur text-black text-[10px] font-bold px-2 py-0.5 rounded-sm uppercase tracking-wider shadow-lg">Gợi ý</div>
                                        
                                        <!-- Play Button -->
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                                            <div class="w-12 h-12 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center border border-phim-yellow/50 shadow-[0_0_20px_rgba(234,179,8,0.3)]">
                                                <i data-lucide="play" class="w-5 h-5 text-phim-yellow fill-phim-yellow ml-1"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Info -->
                                        <div class="absolute bottom-0 left-0 p-3 w-full transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                                            <h3 class="text-white font-bold text-sm truncate drop-shadow-md group-hover:text-phim-yellow transition-colors">${item.name}</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-gray-900 font-bold text-[10px] bg-phim-yellow px-1.5 py-0.5 rounded-sm">${item.year || '2024'}</span>
                                                <span class="text-gray-300 text-[10px] uppercase">${item.quality || 'FHD'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            `;
                        });"""

ai_start = "let html = '';"
ai_end = "});"
content = replace_between(content, ai_start, ai_end, new_ai_html)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("home.js updated")
