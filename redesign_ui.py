import re

def redesign_sliders(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # --- 1. DARK THEME ---
    if 'themes/dark' in file_path:
        # AI Recommend Template
        old_ai = """<a href="/phim/${item.slug}" class="swiper-slide group shrink-0 w-[100px] sm:w-[120px] md:w-[140px] lg:w-[160px] block">
                                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                                        <img loading="lazy" src="${thumb}" alt="${item.name}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <i data-lucide="play-circle" class="w-10 h-10 text-white"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-sm font-medium text-gray-100 line-clamp-1 mb-1 group-hover:text-white">${item.name}</h3>
                                    <p class="text-xs text-gray-500 line-clamp-1">${item.origin_name || ''}</p>
                                </a>"""
        
        new_ai = """<a href="/phim/${item.slug}" class="swiper-slide group shrink-0 w-[130px] sm:w-[150px] md:w-[170px] block">
                                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-zinc-900 mb-3 shadow-lg border border-white/5">
                                        <img loading="lazy" src="${thumb}" alt="${item.name}" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 scale-75 group-hover:scale-100">
                                            <div class="w-12 h-12 bg-red-600/90 rounded-full flex items-center justify-center backdrop-blur-sm shadow-[0_0_15px_rgba(220,38,38,0.5)]">
                                                <i data-lucide="play" class="w-6 h-6 text-white ml-1"></i>
                                            </div>
                                        </div>
                                        <div class="absolute top-2 left-2 bg-red-600/90 backdrop-blur text-white text-[10px] font-bold px-2 py-0.5 rounded shadow">Gợi Ý</div>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-100 line-clamp-1 mb-1 group-hover:text-red-500 transition-colors">${item.name}</h3>
                                    <p class="text-xs text-gray-500 line-clamp-1">${item.origin_name || 'Đang cập nhật'}</p>
                                </a>"""
        
        # Replace JS template
        content = content.replace(old_ai, new_ai)
        
        # Fix the skeleton for AI Recommend to match new size
        content = re.sub(
            r'<div class="swiper-slide group shrink-0 w-\[100px\] sm:w-\[120px\] md:w-\[140px\] lg:w-\[160px\] block">([\s\S]*?)</div>\n                            <div class="h-4',
            r'<div class="swiper-slide group shrink-0 w-[130px] sm:w-[150px] md:w-[170px] block">\1</div>\n                            <div class="h-4',
            content
        )

        # Ranking Template
        old_rank = """<a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="swiper-slide group shrink-0 w-[100px] sm:w-[120px] md:w-[140px] lg:w-[160px] block relative">
                        <div class="absolute -left-3 -bottom-4 text-6xl md:text-8xl font-black <?= $rankColor ?> opacity-80 z-20" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8); -webkit-text-stroke: 1px #fff;"><?= $rank ?></div>
                        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-2 z-10 ml-4">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <i data-lucide="play-circle" class="w-10 h-10 text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-100 line-clamp-1 group-hover:text-white"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-xs text-gray-500"><?= number_format($views) ?> views</p>
                        </div>
                    </a>"""
                    
        new_rank = """<a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="swiper-slide group shrink-0 w-[150px] sm:w-[180px] md:w-[200px] block relative flex items-end pt-8">
                        <div class="absolute left-0 -bottom-2 text-[100px] sm:text-[120px] md:text-[140px] font-black leading-none z-0 tracking-tighter select-none"
                             style="color: #09090b; -webkit-text-stroke: 2px <?= $rank <= 3 ? '#ef4444' : '#52525b' ?>; text-shadow: 4px 4px 10px rgba(0,0,0,0.8);">
                            <?= $rank ?>
                        </div>
                        <div class="relative w-[110px] sm:w-[130px] md:w-[150px] aspect-[2/3] rounded-xl overflow-hidden z-10 ml-8 sm:ml-12 md:ml-16 shadow-[0_10px_20px_rgba(0,0,0,0.6)] border border-white/10 group-hover:-translate-y-3 transition-transform duration-300">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" decoding="async" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 scale-75 group-hover:scale-100">
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md border border-white/30">
                                    <i data-lucide="play" class="w-5 h-5 text-white ml-1"></i>
                                </div>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-2 group-hover:translate-y-0">
                                <h3 class="text-xs font-bold text-white line-clamp-2 leading-tight drop-shadow-md"><?= htmlspecialchars($item['name']) ?></h3>
                            </div>
                        </div>
                    </a>"""
        content = content.replace(old_rank, new_rank)


    # --- 2. PHIMHAYOK THEME ---
    if 'themes/phimhayok' in file_path:
        # Just update the widths to be a bit better.
        content = re.sub(r'w-\[110px\] sm:w-\[130px\] md:w-\[150px\]', 'w-[130px] sm:w-[150px] md:w-[170px]', content)

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

redesign_sliders('themes/dark/index.php')
redesign_sliders('themes/phimhayok/index.php')
