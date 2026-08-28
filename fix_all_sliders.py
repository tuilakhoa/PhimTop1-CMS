import re

file_path = 'themes/dark/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# I will find all instances of standard slider blocks
pattern = re.compile(r'<a href="<\?= \$historyLink \?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 block">(.*?)</a>', re.DOTALL)

new_standard = """<a href="<?= $historyLink ?>" class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 lg:w-56 block transition-transform duration-500 hover:scale-105 hover:z-50">
                        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-gray-900 border border-white/5 shadow-lg group-hover:shadow-2xl group-hover:shadow-red-500/20 transition-all duration-500">
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

content = pattern.sub(new_standard.replace('\\', '\\\\'), content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("All standard sliders updated")
