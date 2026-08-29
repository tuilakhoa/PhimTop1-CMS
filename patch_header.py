import re

with open('themes/phimhayok/header.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the 3 dropdowns with 1 mega menu
start_dropdowns = "<!-- Dropdowns -->"
end_dropdowns = "</div>\n            </div>\n            \n            <!-- Right: Search & Login -->"

start_idx = content.find(start_dropdowns)
end_idx = content.find(end_dropdowns)

mega_menu = """<!-- Dropdowns -->
                    <div class="relative group">
                        <button class="hover:text-white flex items-center transition-colors py-6">
                            <i data-lucide="compass" class="w-4 h-4 mr-1.5"></i> Khám phá <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div class="absolute top-[100%] left-0 w-[700px] bg-[#141414] border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-6 flex gap-6">
                            
                            <!-- Thể loại -->
                            <div class="flex-1">
                                <h3 class="text-white font-bold mb-3 flex items-center border-b border-gray-800 pb-2"><i data-lucide="list" class="w-4 h-4 mr-2 text-phim-yellow"></i>Thể loại</h3>
                                <div class="grid grid-cols-2 gap-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                                    <?php foreach ($genres as $g): ?>
                                        <a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/<?= htmlspecialchars($g['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded transition-colors truncate"><?= htmlspecialchars($g['name']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Quốc gia -->
                            <div class="w-48">
                                <h3 class="text-white font-bold mb-3 flex items-center border-b border-gray-800 pb-2"><i data-lucide="globe" class="w-4 h-4 mr-2 text-phim-yellow"></i>Quốc gia</h3>
                                <div class="grid grid-cols-1 gap-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                                    <?php foreach ($countries as $c): ?>
                                        <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/<?= htmlspecialchars($c['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded transition-colors truncate"><?= htmlspecialchars($c['name']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Năm -->
                            <div class="w-32">
                                <h3 class="text-white font-bold mb-3 flex items-center border-b border-gray-800 pb-2"><i data-lucide="calendar" class="w-4 h-4 mr-2 text-phim-yellow"></i>Năm</h3>
                                <div class="grid grid-cols-2 gap-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                                    <?php for($y = date('Y'); $y >= 2010; $y--): ?>
                                        <a href="/nam/<?= $y ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded text-center transition-colors"><?= $y ?></a>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    """

if start_idx != -1 and end_idx != -1:
    content = content[:start_idx] + mega_menu + content[end_idx:]
    with open('themes/phimhayok/header.php', 'w', encoding='utf-8') as f:
        f.write(content)
    print("Patched header menu")
else:
    print("Could not find dropdowns")
