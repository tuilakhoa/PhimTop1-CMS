import re

with open('themes/phimhayok/index.php', 'r') as f:
    content = f.read()

# We want to find the whole block from "<!-- 4. Phim Nổi Bật (Mixed layout) -->"
# to the end of "<!-- Phim HÀN QUỐC Section -->... </section> <?php endif; ?>"
# and replace it.

start_marker = "<!-- 4. Phim Nổi Bật (Mixed layout) -->"
end_marker = "<!-- 5. Bảng Xếp Hạng (Leaderboard) -->"

start_idx = content.find(start_marker)
end_idx = content.find(end_marker)

if start_idx == -1 or end_idx == -1:
    print("Could not find markers")
    exit(1)

new_sections = """<!-- KHỐI PHIM QUỐC GIA (NỔI BẬT, ÂU MỸ, TRUNG QUỐC, HÀN QUỐC) - Gom lại cho gần nhau -->
    <div class="space-y-12 lg:space-y-16">
    
        <!-- 4. Phim Nổi Bật -->
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-phim-yellow uppercase mt-1">Nổi Bật</h3>
                </div>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-moi" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($movies, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['poster_url']) ? $item['poster_url'] : ($item['thumb_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Phim ÂU MỸ Section -->
        <?php if (!empty($auMyData)): ?>
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-cyan-400 uppercase mt-1">ÂU MỸ</h3>
                </div>
                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/au-my" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($auMyData, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm shadow-md"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-cyan-400 transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Phim TRUNG QUỐC Section -->
        <?php if (!empty($trungQuocData)): ?>
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-red-500 uppercase mt-1">TRUNG QUỐC</h3>
                </div>
                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/trung-quoc" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($trungQuocData, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm shadow-md"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-red-500 transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Phim HÀN QUỐC Section -->
        <?php if (!empty($hanQuocData)): ?>
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-[#5b61f4] uppercase mt-1">HÀN QUỐC</h3>
                </div>
                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/han-quoc" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($hanQuocData, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm shadow-md"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-[#5b61f4] transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
    </div>

    """

new_content = content[:start_idx] + new_sections + content[end_idx:]

with open('themes/phimhayok/index.php', 'w') as f:
    f.write(new_content)

print("Replaced sections successfully")

