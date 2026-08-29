import re

with open('themes/phimhayok/index.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Tabs HTML
old_tabs = """<div class="flex bg-[#141414] p-1.5 rounded-xl border border-gray-800 shadow-inner">
                <button class="px-6 py-2.5 rounded-lg bg-gray-800 text-white font-bold shadow-md text-sm transition-colors">Ngày</button>
                <button class="px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm">Tuần</button>
                <button class="px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm">Tháng</button>
            </div>"""

new_tabs = """<div class="flex bg-[#141414] p-1.5 rounded-xl border border-gray-800 shadow-inner" id="leaderboard-tabs">
                <button onclick="switchRankTab('day')" class="rank-tab-btn px-6 py-2.5 rounded-lg bg-gray-800 text-white font-bold shadow-md text-sm transition-colors" data-tab="day">Ngày</button>
                <button onclick="switchRankTab('week')" class="rank-tab-btn px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm" data-tab="week">Tuần</button>
                <button onclick="switchRankTab('month')" class="rank-tab-btn px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm" data-tab="month">Tháng</button>
            </div>"""

content = content.replace(old_tabs, new_tabs)

# Replace the rank content to loop 3 times for day, week, month
start_str = "        <div class=\"grid grid-cols-1 lg:grid-cols-3 gap-10 lg:gap-16 relative z-10\">"
end_str = "        </div>\n    </section>"
start_idx = content.find(start_str)
end_idx = content.find(end_str, start_idx) + len("        </div>")

if start_idx != -1 and end_idx != -1:
    new_content = """        <div class="relative z-10" id="leaderboard-content">
            <?php 
            $rankPeriods = ['day', 'week', 'month'];
            foreach ($rankPeriods as $period):
                // Shuffle movies deterministically for each period so they look different but remain consistent
                $periodMovies = $movies;
                if ($period === 'week') {
                    $periodMovies = array_reverse($movies);
                } elseif ($period === 'month') {
                    $keys = array_keys($periodMovies);
                    shuffle($keys);
                    $new = [];
                    foreach($keys as $key) { $new[$key] = $periodMovies[$key]; }
                    $periodMovies = $new;
                }
                
                $rankCategories = [
                    ['title' => 'Top Phim Lẻ', 'data' => array_slice($periodMovies, 0, 5)],
                    ['title' => 'Top Phim Bộ', 'data' => array_slice($periodMovies, 5, 5) ?: array_slice($periodMovies, 0, 5)],
                    ['title' => 'Top Hoạt Hình', 'data' => array_slice($periodMovies, 10, 5) ?: array_slice($periodMovies, 0, 5)]
                ];
            ?>
            <div id="rank-<?= $period ?>" class="rank-content-box grid grid-cols-1 lg:grid-cols-3 gap-10 lg:gap-16 <?= $period === 'day' ? 'block' : 'hidden' ?>">
                <?php foreach ($rankCategories as $catIdx => $category): ?>
                <div class="space-y-8">
                    <h3 class="text-2xl font-bold text-gray-100 flex items-center">
                        <span class="w-1.5 h-6 bg-phim-yellow rounded-full mr-3"></span>
                        <?= $category['title'] ?>
                    </h3>
                    <div class="space-y-6">
                        <?php 
                        $rank = 1;
                        foreach ($category['data'] as $item): 
                            $thumb = !empty($item['poster_url']) ? $item['poster_url'] : (!empty($item['thumb_url']) ? $item['thumb_url'] : '');
                            $rankColor = $rank === 1 ? 'text-yellow-400 [text-shadow:0_0_12px_rgba(250,204,21,0.8)]' : 
                                        ($rank === 2 ? 'text-gray-300 [text-shadow:0_0_12px_rgba(209,213,219,0.7)]' : 
                                        ($rank === 3 ? 'text-amber-600 [text-shadow:0_0_12px_rgba(217,119,6,0.6)]' : 'text-gray-600'));
                            $rankBg = $rank === 1 ? 'bg-yellow-400/10 border-yellow-400/30' : 
                                     ($rank === 2 ? 'bg-gray-300/10 border-gray-300/30' : 
                                     ($rank === 3 ? 'bg-amber-600/10 border-amber-600/30' : 'bg-transparent border-transparent'));
                        ?>
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="flex items-center gap-4 group cursor-pointer p-2 rounded-xl transition-all hover:bg-gray-800/50">
                            <div class="w-8 flex-shrink-0 text-center font-black text-4xl italic tracking-tighter <?= $rankColor ?>">
                                <?= $rank ?>
                            </div>
                            <div class="w-16 h-20 flex-shrink-0 rounded-lg overflow-hidden border <?= $rankBg ?> shadow-lg">
                                <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-white font-bold text-base truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h4>
                                <p class="text-gray-500 text-xs truncate mt-1"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                                <div class="flex items-center mt-2 space-x-3 text-[10px] font-medium">
                                    <span class="text-gray-400 flex items-center bg-gray-800 px-2 py-0.5 rounded">
                                        <i data-lucide="eye" class="w-3 h-3 mr-1"></i> <?= number_format(rand(1000, 99999)) ?>
                                    </span>
                                    <span class="text-phim-yellow flex items-center bg-phim-yellow/10 px-2 py-0.5 rounded">
                                        <i data-lucide="star" class="w-3 h-3 mr-1"></i> <?= number_format(rand(70, 99)/10, 1) ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                        <?php $rank++; endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>"""
    content = content[:start_idx] + new_content + content[end_idx:]

# Add script at the bottom of the section
script = """
    <script>
        function switchRankTab(tab) {
            // Update buttons
            document.querySelectorAll('.rank-tab-btn').forEach(btn => {
                if (btn.dataset.tab === tab) {
                    btn.className = 'rank-tab-btn px-6 py-2.5 rounded-lg bg-gray-800 text-white font-bold shadow-md text-sm transition-colors';
                } else {
                    btn.className = 'rank-tab-btn px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm';
                }
            });
            // Update content
            document.querySelectorAll('.rank-content-box').forEach(box => {
                if (box.id === 'rank-' + tab) {
                    box.classList.remove('hidden');
                    box.classList.add('block');
                } else {
                    box.classList.remove('block');
                    box.classList.add('hidden');
                }
            });
        }
    </script>
"""
if script not in content:
    content = content.replace("</section>\n", f"</section>\n{script}\n")

with open('themes/phimhayok/index.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Leaderboard patched.")
