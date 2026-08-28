import re

file_path = 'themes/phimhayok/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add Âu Mỹ data fetching
content = content.replace("$hanQuocData = fetchApiFilms('quoc-gia', 'han-quoc', 1)['items'] ?? [];", "$hanQuocData = fetchApiFilms('quoc-gia', 'han-quoc', 1)['items'] ?? [];\n$auMyData = fetchApiFilms('quoc-gia', 'au-my', 1)['items'] ?? [];")

def make_section(title_prefix, title_highlight, highlight_color, slug_type, slug_name, data_var):
    return f"""
    <!-- Phim {title_highlight} Section -->
    <?php if (!empty({data_var})): ?>
    <section class="flex flex-col md:flex-row gap-4 md:gap-8 items-start border-t border-gray-900 pt-8 mt-8">
        <div class="md:w-32 lg:w-48 shrink-0 flex flex-col justify-center h-full pt-4 md:pt-[10%]">
            <h2 class="text-3xl md:text-4xl font-bold text-white leading-tight">{title_prefix} <br><span class="{highlight_color} uppercase">{title_highlight}</span></h2>
            <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/{slug_name}" class="text-gray-500 hover:text-white text-sm mt-4 flex items-center transition-colors">Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i></a>
        </div>
        
        <div class="flex-1 min-w-0 w-full relative">
            <div class="swiper swiper-horizontal">
                <div class="swiper-wrapper pb-4">
                    <?php foreach (array_slice({data_var}, 0, 10) as $item): ?>
                        <div class="swiper-slide w-72 md:w-80">
                            <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-2">
                                <!-- 16:9 Aspect Ratio Image -->
                                <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                                    <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>"
                                         class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                                    
                                    <!-- Top Left Yellow Tag -->
                                    <?php if (!empty($item['episode_current'])): ?>
                                        <div class="absolute top-2 left-2">
                                            <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm shadow-md">
                                                <?= htmlspecialchars($item['episode_current'] ?? '') ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-3">
                                    <h3 class="text-white font-medium text-sm md:text-base truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                                    <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
"""

new_sections = make_section("Phim", "ÂU MỸ", "text-cyan-400", "quoc-gia", "au-my", "$auMyData")

content = content.replace('<!-- Phim TRUNG QUỐC Section -->', new_sections + '\n    <!-- Phim TRUNG QUỐC Section -->')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Âu Mỹ section added")
