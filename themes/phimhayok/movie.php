<?php include __DIR__ . '/header.php'; 

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://phimimg.com/';
if (!empty($movie['category']) && is_array($movie['category'])) {
    $firstCat = is_array($movie['category'][0]) ? ($movie['category'][0]['slug'] ?? '') : '';
    if ($firstCat) {
        $sugRes = @file_get_contents("https://phimapi.com/v1/api/the-loai/" . urlencode($firstCat) . "?limit=12");
        if ($sugRes) {
            $sugData = json_decode($sugRes, true);
            if (isset($sugData['data']['items'])) {
                $suggestions = $sugData['data']['items'];
                $sugDomain = $sugData['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
            }
        }
    }
}

// Fetch images gallery
$movieImages = ['backdrops' => [], 'posters' => []];
$tmdbId = $movie['tmdb']['id'] ?? null;
$tmdbType = $movie['tmdb']['type'] ?? 'movie';
$tmdbApiKey = $settings['tmdbApiKey'] ?? '';

if ($tmdbId && $tmdbApiKey) {
    // Fetch directly from TMDB using the provided API Key
    $tmdbRes = @file_get_contents("https://api.themoviedb.org/3/{$tmdbType}/{$tmdbId}/images?api_key=" . urlencode($tmdbApiKey));
    if ($tmdbRes) {
        $tmdbData = json_decode($tmdbRes, true);
        if (isset($tmdbData['backdrops'])) $movieImages['backdrops'] = $tmdbData['backdrops'];
        if (isset($tmdbData['posters'])) $movieImages['posters'] = $tmdbData['posters'];
    }
} else {
    // Fallback to PhimAPI
    $imgRes = @file_get_contents("https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/images");
    if ($imgRes) {
        $imgData = json_decode($imgRes, true);
        if (isset($imgData['data'])) {
            $movieImages['backdrops'] = $imgData['data']['backdrops'] ?? [];
            $movieImages['posters'] = $imgData['data']['posters'] ?? [];
        }
    }
}

// Extract TMDB info
$tmdbVote = $movie['tmdb']['vote_average'] ?? 0;
$tmdbCount = $movie['tmdb']['vote_count'] ?? 0;
?>

<!-- Backdrop Header -->
<div class="relative w-full h-[50vh] overflow-hidden -mt-20">
    <div class="absolute inset-0">
        <img src="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : $movie['thumb_url']) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/80 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0a] via-[#0a0a0a]/50 to-transparent"></div>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1200px] -mt-[30vh] md:-mt-[25vh] relative z-10 mb-16">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Left Sidebar: Poster & Metadata -->
        <div class="w-[240px] md:w-[280px] shrink-0 mx-auto md:mx-0 flex flex-col gap-6">
            <!-- Poster -->
            <div class="rounded-xl overflow-hidden shadow-2xl border border-gray-800 relative group">
                <img src="<?= htmlspecialchars($movie['thumb_url']) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-auto aspect-[3/4] object-cover">
                <div class="absolute top-2 right-2">
                    <span class="bg-[#fcc526] text-black text-[11px] font-bold px-2 py-1 rounded shadow-lg">
                        <?= htmlspecialchars($movie['quality'] ?? 'HD') ?> <?= !empty($movie['lang']) ? '• '.$movie['lang'] : '' ?>
                    </span>
                </div>
            </div>
            
            <!-- Metadata Box -->
            <div class="bg-[#141414] rounded-xl p-5 border border-gray-800 text-sm text-gray-300 flex flex-col gap-3 shadow-lg">
                <div class="flex justify-between border-b border-gray-800 pb-2">
                    <span class="text-gray-500">Trạng thái:</span>
                    <span class="text-[#fcc526] font-medium"><?= htmlspecialchars($movie['episode_current'] ?? 'Đang cập nhật') ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-2">
                    <span class="text-gray-500">Tổng tập:</span>
                    <span class="text-white"><?= htmlspecialchars($movie['episode_total'] ?? 'Đang cập nhật') ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-2">
                    <span class="text-gray-500">Thời lượng:</span>
                    <span class="text-white"><?= htmlspecialchars($movie['time'] ?? 'Đang cập nhật') ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-2">
                    <span class="text-gray-500">Ngôn ngữ:</span>
                    <span class="text-white"><?= htmlspecialchars($movie['lang'] ?? 'Vietsub') ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-2">
                    <span class="text-gray-500">Năm:</span>
                    <span class="text-white"><?= htmlspecialchars($movie['year'] ?? date('Y')) ?></span>
                </div>
                <div class="flex flex-col border-b border-gray-800 pb-2">
                    <span class="text-gray-500 mb-1">Quốc gia:</span>
                    <span class="text-white"><?= htmlspecialchars(is_array($movie['country'] ?? null) ? implode(', ', array_map(function($c) { return is_array($c) ? $c['name'] : $c; }, $movie['country'])) : ($movie['country'] ?? 'Đang cập nhật')) ?></span>
                </div>
                <div class="flex flex-col border-b border-gray-800 pb-2">
                    <span class="text-gray-500 mb-1">Đạo diễn:</span>
                    <span class="text-white"><?= htmlspecialchars(is_array($movie['director'] ?? null) ? implode(', ', $movie['director']) : ($movie['director'] ?? 'Đang cập nhật')) ?></span>
                </div>
                <div class="flex flex-col pb-1">
                    <span class="text-gray-500 mb-1">Diễn viên:</span>
                    <span class="text-white line-clamp-3 hover:line-clamp-none transition-all"><?= htmlspecialchars(is_array($movie['actor'] ?? null) ? implode(', ', $movie['actor']) : ($movie['actor'] ?? 'Đang cập nhật')) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Right Content: Info & Episodes -->
        <div class="flex-1 text-white pt-2 md:pt-4">
            <!-- Title -->
            <h1 class="text-3xl md:text-5xl font-bold mb-2 text-white leading-tight drop-shadow-md"><?= htmlspecialchars($movie['name']) ?></h1>
            <h2 class="text-lg text-gray-400 mb-4 italic"><?= htmlspecialchars($movie['origin_name']) ?> (<?= htmlspecialchars($movie['year'] ?? date('Y')) ?>)</h2>
            
            <!-- Genres -->
            <div class="flex flex-wrap gap-2 mb-6">
                <?php 
                $cats = is_array($movie['category'] ?? null) ? $movie['category'] : [];
                foreach ($cats as $cat): 
                    $catName = is_array($cat) ? ($cat['name'] ?? '') : $cat;
                ?>
                    <a href="#" class="px-3 py-1 bg-[#202020] text-gray-300 text-xs rounded hover:bg-[#303030] transition-colors border border-gray-800"><?= htmlspecialchars($catName) ?></a>
                <?php endforeach; ?>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-wrap items-center gap-3 mb-8 bg-[#141414]/50 p-4 rounded-2xl border border-gray-800/50 backdrop-blur-sm">
                <?php if (!empty($episodes[0]['server_data'])): ?>
                    <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($episodes[0]['server_data'][0]['slug']) ?>" 
                       class="px-8 py-2.5 bg-[#fcc526] hover:bg-yellow-500 text-black font-bold rounded-full transition-all hover:scale-105 flex items-center shadow-lg shadow-yellow-500/20">
                        Xem ngay
                    </a>
                <?php else: ?>
                    <button class="px-8 py-2.5 bg-gray-700 text-gray-400 font-bold rounded-full cursor-not-allowed">
                        Đang Cập Nhật
                    </button>
                <?php endif; ?>
                
                <button class="px-6 py-2.5 bg-[#303030] hover:bg-[#404040] text-white text-sm font-medium rounded-full transition-colors flex items-center border border-gray-700">
                    <i data-lucide="send" class="w-4 h-4 mr-2"></i> Chia sẻ
                </button>
                
                <?php if ($tmdbVote > 0): ?>
                <div class="ml-auto bg-[#1e293b] text-white px-4 py-2 rounded-lg flex items-center font-bold text-sm border border-blue-900/50 shadow-inner">
                    <i data-lucide="star" class="w-4 h-4 mr-1.5 text-[#fcc526] fill-current"></i>
                    <?= number_format($tmdbVote, 1) ?> <span class="text-blue-200 text-xs font-normal ml-1">/10 Đánh giá</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
<div class="mb-10" id="comments-section" data-slug="<?= htmlspecialchars($slug) ?>">
                <h3 class="text-xl font-bold mb-4 flex items-center text-white">
                    <span class="w-1 h-5 bg-[#fcc526] mr-2 rounded"></span> Giới thiệu:
                </h3>
                <div class="text-gray-400 text-sm leading-relaxed whitespace-pre-line bg-[#141414] p-5 rounded-xl border border-gray-800 shadow-inner">
                    <?= nl2br(htmlspecialchars(strip_tags($movie['content'] ?? 'Chưa có nội dung mô tả cho phim này.'))) ?>
                </div>
            </div>
            
            <!-- Cast / Peoples Component -->
            <?php include __DIR__ . '/components/actors.php'; ?>
            
            <!-- Episodes List -->
<div class="mb-10" id="comments-section" data-slug="<?= htmlspecialchars($slug) ?>">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">Danh sách tập</h3>
                    <button class="text-gray-400 hover:text-white text-sm flex items-center transition-colors">
                        <i data-lucide="arrow-down-up" class="w-4 h-4 mr-1"></i> Sắp xếp
                    </button>
                </div>
                
                <div class="bg-[#141414] rounded-xl p-5 border border-gray-800 shadow-lg">
                    <h4 class="text-[#fcc526] font-medium mb-4 flex items-center">
                        <i data-lucide="menu" class="w-4 h-4 mr-2"></i> Phần 1
                    </h4>
                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2.5">
                        <?php 
                        $server = $episodes[0] ?? ['server_data' => []];
                        foreach ($server['server_data'] as $ep): 
                        ?>
                            <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($ep['slug']) ?>" 
                               class="px-2 py-2.5 bg-[#202020] hover:bg-[#fcc526] hover:text-black text-gray-300 text-sm font-medium rounded transition-all text-center truncate border border-gray-800"
                               title="<?= htmlspecialchars($ep['name']) ?>">
                                <?= htmlspecialchars($ep['name']) ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if(empty($server['server_data'])): ?>
                            <div class="col-span-full text-gray-500 text-sm py-4">Chưa có tập phim nào được cập nhật.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Comments (Dynamic UI) -->
<div class="mb-10" id="comments-section" data-slug="<?= htmlspecialchars($slug) ?>">
                <h3 class="text-xl font-bold mb-4 flex items-center text-white">
                    <i data-lucide="message-square" class="w-5 h-5 mr-2 text-[#fcc526]"></i> Bình luận (<span id="comment-count">0</span>)
                </h3>
                
                <div class="bg-[#141414] rounded-xl p-5 border border-gray-800 shadow-lg">
                    <div class="relative bg-[#202020] rounded-lg p-3 border border-gray-700">
                        <input type="text" id="comment-name" class="w-full bg-transparent text-white text-sm outline-none mb-2 pb-2 border-b border-gray-700 hidden" placeholder="Nhập tên của bạn...">
                        <textarea id="comment-content" rows="3" class="w-full bg-transparent text-white text-sm outline-none resize-none placeholder-gray-500" placeholder="Vui lòng nhập nội dung..."></textarea>
                        <div class="flex items-center justify-between mt-2 border-t border-gray-700 pt-3">
                            <label class="flex items-center text-gray-400 text-sm cursor-pointer hover:text-white transition-colors">
                                <input type="checkbox" id="comment-anon" checked class="mr-2 rounded border-gray-600 bg-gray-700 text-[#fcc526] focus:ring-[#fcc526]"> Ẩn danh ?
                            </label>
                            <button id="btn-submit-comment" class="bg-[#5c4a16] text-[#fcc526] font-bold px-4 py-1.5 rounded-lg text-sm flex items-center hover:bg-[#7a621c] transition-colors border border-[#7a621c]">
                                Gửi bình luận <i data-lucide="send" class="w-4 h-4 ml-1.5"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="comments-list" class="mt-6 space-y-5">
                        <div class="text-center text-gray-500 text-sm py-4">Đang tải bình luận...</div>
                    </div>
                </div>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const anonCheckbox = document.getElementById('comment-anon');
                const nameInput = document.getElementById('comment-name');
                const contentInput = document.getElementById('comment-content');
                const submitBtn = document.getElementById('btn-submit-comment');
                const commentsList = document.getElementById('comments-list');
                const countSpan = document.getElementById('comment-count');
                const movieSlug = '<?= htmlspecialchars($slug) ?>';
                
                anonCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        nameInput.classList.add('hidden');
                    } else {
                        nameInput.classList.remove('hidden');
                        nameInput.focus();
                    }
                });
                
                function fetchComments() {
                    fetch('/api/comments.php?slug=' + movieSlug)
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                countSpan.textContent = res.data.length;
                                if (res.data.length === 0) {
                                    commentsList.innerHTML = '<div class="text-center text-gray-500 text-sm py-4">Chưa có bình luận nào. Hãy là người đầu tiên!</div>';
                                    return;
                                }
                                
                                let html = '';
                                res.data.forEach(c => {
                                    html += `
                                        <div class="flex gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center shrink-0 border border-gray-700">
                                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-baseline gap-2 mb-1">
                                                    <span class="font-bold text-gray-200 text-sm">${c.user_name}</span>
                                                    <span class="text-xs text-gray-500">${c.time_ago}</span>
                                                </div>
                                                <p class="text-sm text-gray-300">${c.content}</p>
                                            </div>
                                        </div>
                                    `;
                                });
                                commentsList.innerHTML = html;
                            }
                        });
                }
                
                submitBtn.addEventListener('click', function() {
                    const content = contentInput.value.trim();
                    const isAnon = anonCheckbox.checked;
                    const name = isAnon ? '' : nameInput.value.trim();
                    
                    if (!content) return alert('Vui lòng nhập nội dung bình luận!');
                    if (!isAnon && !name) return alert('Vui lòng nhập tên của bạn!');
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Đang gửi...';
                    
                    fetch('/api/comments.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({slug: movieSlug, name: name, content: content, anonymous: isAnon})
                    })
                    .then(res => res.json())
                    .then(res => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Gửi bình luận <svg class="w-4 h-4 ml-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
                        if (res.success) {
                            contentInput.value = '';
                            fetchComments();
                        } else {
                            alert(res.message);
                        }
                    });
                });
                
                fetchComments();
            });
            </script>

            
        </div>
    </div>
</div>

<!-- Movie Suggestions -->
<?php if (!empty($suggestions)): ?>
<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1200px] mb-16 relative z-10">
    <h3 class="text-2xl font-bold mb-6 flex items-center text-white">
        <i data-lucide="flame" class="w-6 h-6 mr-2 text-red-500"></i> Đề xuất phim liên quan
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        <?php foreach ($suggestions as $item): ?>
            <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group block relative overflow-hidden rounded-xl bg-transparent transition-all duration-300">
                <div class="aspect-[3/4] relative overflow-hidden rounded-xl border border-transparent group-hover:border-gray-700">
                    <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    
                    <!-- Vietsub Badge (Yellow top right) -->
                    <div class="absolute top-2 right-2">
                        <span class="bg-[#fcc526] text-black text-[10px] font-bold px-2 py-0.5 rounded shadow-lg uppercase">
                            <?= htmlspecialchars($item['lang'] ?? 'Vietsub') ?>
                        </span>
                    </div>
                    
                    <!-- Hot Badge (Orange bottom left) -->
                    <div class="absolute bottom-2 left-2">
                        <span class="bg-[#ff4d00] text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg uppercase tracking-wider">
                            Hot
                        </span>
                    </div>
                    
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="w-12 h-12 bg-black/60 rounded-full flex items-center justify-center border border-gray-500 transform group-hover:scale-110 transition-transform backdrop-blur-sm">
                            <i data-lucide="play" class="w-5 h-5 text-white ml-1"></i>
                        </div>
                    </div>
                </div>
                <div class="pt-3 pb-2 px-1">
                    <h3 class="text-white font-medium text-sm truncate group-hover:text-[#fcc526] transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-xs text-gray-500 truncate mt-0.5"><?= htmlspecialchars($item['origin_name']) ?> <span class="bg-[#202020] px-1.5 py-0.5 rounded ml-1"><?= htmlspecialchars($item['year'] ?? date('Y')) ?></span></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
