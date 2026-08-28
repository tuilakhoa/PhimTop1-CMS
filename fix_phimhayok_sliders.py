import re

file_path = 'themes/phimhayok/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

def replace_between(text, start_str, end_str, new_content):
    start = text.find(start_str)
    if start == -1: return text
    end = text.find(end_str, start)
    if end == -1: return text
    return text[:start] + new_content + text[end + len(end_str):]

# 1. AI Recommend
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

# 2. Ranking
new_ranking = """<a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 lg:w-56 block relative transition-transform duration-500 hover:scale-105 hover:z-50">
                        <!-- Huge Number Netflix Style -->
                        <div class="absolute -left-2 -bottom-2 md:-left-4 md:-bottom-4 text-[100px] sm:text-[120px] md:text-[140px] font-black text-black z-0 leading-none tracking-tighter select-none pointer-events-none group-hover:text-gray-900 transition-colors duration-300" 
                             style="-webkit-text-stroke: 2px <?= $rank <= 3 ? '#eab308' : '#4b5563' ?>; text-shadow: 0 10px 30px rgba(0,0,0,0.8);">
                            <?= $rank ?>
                        </div>
                        
                        <!-- Poster Card -->
                        <div class="relative w-[85%] aspect-[2/3] ml-auto overflow-hidden rounded-xl bg-gray-900 mb-2 z-10 shadow-[0_10px_20px_rgba(0,0,0,0.6)] group-hover:shadow-2xl group-hover:shadow-phim-yellow/20 transition-all duration-500">
                            <img src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Play Button -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                                <div class="w-12 h-12 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center border border-phim-yellow/50 shadow-[0_0_20px_rgba(234,179,8,0.3)]">
                                    <i data-lucide="play" class="w-5 h-5 text-phim-yellow fill-phim-yellow ml-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="pl-8 md:pl-10 relative z-20">
                            <h3 class="text-gray-200 group-hover:text-phim-yellow font-bold text-xs sm:text-sm truncate transition-colors drop-shadow-md"><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="flex items-center gap-1.5 mt-1 text-[10px] sm:text-xs">
                                <i data-lucide="eye" class="w-3 h-3 text-phim-yellow"></i>
                                <span class="text-gray-400"><?= number_format($views) ?></span>
                            </div>
                        </div>
                    </a>"""

rank_start = """<a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 lg:w-56 block relative cursor-pointer">"""
if rank_start in content:
    content = replace_between(content, rank_start, "</a>", new_ranking)
else:
    # Just use regex if exact match fails
    import re
    content = re.sub(r'<a href="/<\?= \$settings\["slugMovie"\] \?\? "phim" \?>/<\?= urlencode\(\$item\[\'slug\'\]\) \?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 lg:w-56 block relative cursor-pointer">.*?</a>', new_ranking, content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("PhimHayOK Sliders updated")
