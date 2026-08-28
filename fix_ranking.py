import re

file_path = 'themes/dark/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

def replace_between(text, start_str, end_str, new_content):
    start = text.find(start_str)
    if start == -1: return text
    end = text.find(end_str, start)
    if end == -1: return text
    return text[:start] + new_content + text[end + len(end_str):]

new_ranking = """<a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 lg:w-56 block relative transition-transform duration-500 hover:scale-105 hover:z-50">
                        <!-- Huge Number Netflix Style -->
                        <div class="absolute -left-2 -bottom-2 md:-left-4 md:-bottom-4 text-[100px] sm:text-[120px] md:text-[140px] font-black text-black z-0 leading-none tracking-tighter select-none pointer-events-none group-hover:text-gray-900 transition-colors duration-300" 
                             style="-webkit-text-stroke: 2px <?= $rank <= 3 ? '#dc2626' : '#4b5563' ?>; text-shadow: 0 10px 30px rgba(0,0,0,0.8);">
                            <?= $rank ?>
                        </div>
                        
                        <!-- Poster Card -->
                        <div class="relative w-[85%] aspect-[2/3] ml-auto overflow-hidden rounded-xl bg-gray-900 border border-white/5 mb-2 z-10 shadow-[0_10px_20px_rgba(0,0,0,0.6)] group-hover:shadow-2xl group-hover:shadow-red-500/20 transition-all duration-500">
                            <img src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Play Button -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                                <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/40 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                                    <i data-lucide="play" class="w-5 h-5 text-white fill-white ml-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="pl-8 md:pl-10 relative z-20">
                            <h3 class="text-gray-200 group-hover:text-white font-bold text-xs sm:text-sm truncate transition-colors drop-shadow-md"><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="flex items-center gap-1.5 mt-1 text-[10px] sm:text-xs">
                                <i data-lucide="eye" class="w-3 h-3 text-red-500"></i>
                                <span class="text-gray-400"><?= number_format($views) ?></span>
                            </div>
                        </div>
                    </a>"""

start_str = """<a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 block relative">"""
end_str = """</a>"""

content = replace_between(content, start_str, end_str, new_ranking)

# Also fix standard list sliders
new_standard = """<a href="<?= $historyLink ?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 lg:w-56 block transition-transform duration-500 hover:scale-105 hover:z-50">
                        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-gray-900 border border-white/5 mb-3 shadow-lg group-hover:shadow-2xl group-hover:shadow-red-500/20 transition-all duration-500">
                            <img src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Badges -->
                            <div class="absolute top-2 left-2 flex flex-col gap-1">
                                <?php if (!empty($item['quality'])): ?>
                                    <span class="bg-red-600/90 backdrop-blur text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-lg"><?= htmlspecialchars($item['quality']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Play Button -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                                <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/40 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                                    <i data-lucide="play" class="w-5 h-5 text-white fill-white ml-1"></i>
                                </div>
                            </div>
                            
                            <!-- Bottom Info -->
                            <div class="absolute bottom-0 left-0 p-3 w-full transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                                <h3 class="text-white font-bold text-sm truncate drop-shadow-md"><?= htmlspecialchars($item['name']) ?></h3>
                                <?php if (!empty($item['origin_name'])): ?>
                                    <div class="text-gray-400 text-[11px] truncate mt-0.5"><?= htmlspecialchars($item['origin_name']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>"""

start_str_std = """<a href="<?= $historyLink ?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 block">"""

content = replace_between(content, start_str_std, end_str, new_standard)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Sliders updated")
