<?php
if (!$movie) {
    die("Phim không tồn tại.");
}

$ep = $_GET['ep'] ?? ''; // Not used for playback here anymore

include __DIR__ . '/header.php';

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://phimimg.com/';
if (!empty($movie['category']) && is_array($movie['category'])) {
    $firstCatObj = reset($movie['category']);
    $firstCat = is_array($firstCatObj) ? ($firstCatObj['slug'] ?? '') : (is_string($firstCatObj) ? $firstCatObj : '');
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
    $tmdbRes = @file_get_contents("https://api.themoviedb.org/3/{$tmdbType}/{$tmdbId}/images?api_key=" . urlencode($tmdbApiKey));
    if ($tmdbRes) {
        $tmdbData = json_decode($tmdbRes, true);
        if (isset($tmdbData['backdrops'])) $movieImages['backdrops'] = $tmdbData['backdrops'];
        if (isset($tmdbData['posters'])) $movieImages['posters'] = $tmdbData['posters'];
    }
} else {
    $imgRes = @file_get_contents("https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/images");
    if ($imgRes) {
        $imgData = json_decode($imgRes, true);
        if (isset($imgData['data'])) {
            $movieImages['backdrops'] = $imgData['data']['backdrops'] ?? [];
            $movieImages['posters'] = $imgData['data']['posters'] ?? [];
        }
    }
}

// Extract meta
$tmdbVote = $movie['tmdb']['vote_average'] ?? 0;
$tmdbCount = $movie['tmdb']['vote_count'] ?? 0;

$year = $movie['year'] ?? '';
$country = (!empty($movie['country']) && is_array($movie['country'])) ? $movie['country'][0]['name'] : '';
$episodes_total = $movie['episode_total'] ?? '';
$quality = $movie['quality'] ?? '';
$categories = [];
if (!empty($movie['category']) && is_array($movie['category'])) {
    foreach ($movie['category'] as $c) {
        $categories[] = is_array($c) ? ($c['name'] ?? '') : $c;
    }
}
$categories_str = implode(' - ', array_filter($categories));

$meta_tags = array_filter([$quality, $year, $country, $episodes_total, $categories_str]);

// Get the first episode link for the Play button
$first_ep_link = '#';
if (!empty($episodes[0]['server_data'])) {
    $first_ep_link = '/' . ($settings["slugWatch"] ?? "xem-phim") . '/' . urlencode($slug) . '/' . urlencode($episodes[0]['server_data'][0]['slug']);
}
?>

<div class="bg-[#111319] min-h-screen text-gray-200 font-sans pb-20 pt-[70px] md:pt-[80px]">
    <div class="w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto">
        <div class="flex flex-col xl:flex-row gap-6">
            
            <!-- Left Column: Main Content -->
            <div class="w-full xl:w-[72%]">
                <!-- "Player" Area (Banner) -->
                <div class="relative w-full aspect-video bg-black rounded-lg overflow-hidden mb-6 group">
                    <img src="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : (!empty($movie['thumb_url']) ? $movie['thumb_url'] : '')) ?>" 
                         alt="Poster" class="w-full h-full object-cover opacity-60">
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <?php if ($first_ep_link !== '#'): ?>
                        <a href="<?= $first_ep_link ?>" class="w-16 h-16 md:w-20 md:h-20 bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-[#ff8f00] hover:scale-110 transition-all duration-300">
                            <i data-lucide="play" class="w-8 h-8 md:w-10 md:h-10 ml-2 fill-current"></i>
                        </a>
                        <p class="mt-4 text-white text-lg font-medium drop-shadow-md">Nhấn để xem phim</p>
                        <?php else: ?>
                        <p class="text-white text-lg font-medium drop-shadow-md">Phim chưa có tập nào</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Movie Info -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-2">
                        <h1 class="text-2xl md:text-3xl font-bold text-white"><?= htmlspecialchars($movie['name']) ?></h1>
                        <button class="flex items-center text-gray-400 hover:text-white transition-colors p-2 rounded-md hover:bg-[#22242d]">
                            <i data-lucide="share-2" class="w-4 h-4 mr-1.5"></i> <span class="text-sm">Chia sẻ</span>
                        </button>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 text-[13px] text-gray-400 mb-5">
                        <?php foreach ($meta_tags as $idx => $tag): ?>
                            <?php if ($idx === 0): ?>
                                <span class="border border-gray-600 px-1.5 py-0.5 rounded text-gray-300"><?= htmlspecialchars($tag) ?></span>
                            <?php else: ?>
                                <span>|</span>
                                <span><?= htmlspecialchars($tag) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cast (Inline) -->
                    <div class="mb-5">
                        <?php include __DIR__ . '/components/actors.php'; ?>
                    </div>

                    <!-- Description -->
                    <div class="text-[14px] text-gray-400 leading-relaxed relative">
                        <div id="movie-desc" class="line-clamp-3">
                            <?= !empty($movie['content']) ? strip_tags($movie['content'], '<p><br><b><i>') : 'Chưa có tóm tắt.' ?>
                        </div>
                        <button id="btn-read-more" class="text-[#ff8f00] hover:text-[#ffaa33] text-sm mt-1 focus:outline-none flex items-center font-medium">Đọc thêm <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i></button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const desc = document.getElementById('movie-desc');
                            const btn = document.getElementById('btn-read-more');
                            if (desc && btn) {
                                btn.addEventListener('click', function() {
                                    if (desc.classList.contains('line-clamp-3')) {
                                        desc.classList.remove('line-clamp-3');
                                        btn.innerHTML = 'Thu gọn <i data-lucide="chevron-up" class="w-4 h-4 ml-1"></i>';
                                        lucide.createIcons();
                                    } else {
                                        desc.classList.add('line-clamp-3');
                                        btn.innerHTML = 'Đọc thêm <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>';
                                        lucide.createIcons();
                                    }
                                });
                            }
                        });
                    </script>
                </div>
                
                <!-- Suggestions -->
                <?php if (!empty($suggestions)): ?>
                <div class="mb-10">
                    <h3 class="text-xl font-bold text-white mb-4">Đề xuất cho bạn</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach ($suggestions as $item): ?>
                            <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group flex flex-col">
                                <div class="relative w-full aspect-[16/9] overflow-hidden rounded-md bg-[#22242d] mb-2">
                                    <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute top-1 right-1">
                                        <span class="bg-[#ff4d4f] text-white text-[10px] font-bold px-1.5 py-0.5 rounded"><?= htmlspecialchars($item['quality'] ?? 'HD') ?></span>
                                    </div>
                                    <div class="absolute bottom-1 right-1">
                                        <span class="bg-black/80 text-white text-[11px] px-1.5 py-0.5 rounded"><?= htmlspecialchars($item['episode_current'] ?? 'N/A') ?></span>
                                    </div>
                                </div>
                                <h3 class="text-sm font-medium text-gray-200 line-clamp-1 group-hover:text-[#ff8f00] transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Image Gallery -->
                <?php if (!empty($movieImages['backdrops']) || !empty($movieImages['posters'])): ?>
                <div class="mb-10">
                    <h3 class="text-xl font-bold text-white mb-4">Hình Ảnh Phim</h3>
                    <div class="flex overflow-x-auto gap-4 custom-scrollbar pb-4 snap-x">
                        <?php 
                        $bCount = 0;
                        foreach ($movieImages['backdrops'] as $img): 
                            if ($bCount++ >= 6) break;
                        ?>
                            <div class="shrink-0 w-[240px] md:w-[280px] rounded-md overflow-hidden bg-[#22242d] snap-start border border-[#2d2f36]">
                                <img src="https://image.tmdb.org/t/p/w780<?= htmlspecialchars($img['file_path']) ?>" alt="Backdrop" loading="lazy" class="w-full h-[135px] md:h-[157px] object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Sidebar -->
            <div class="w-full xl:w-[28%] space-y-6">
                
                <?php if (!empty($episodes[0]['server_data'])): ?>
                <!-- Playlist Sidebar -->
                <div class="bg-[#181a20] rounded-lg p-4 border border-[#2d2f36]">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-white leading-tight"><?= htmlspecialchars($movie['name']) ?></h3>
                        <p class="text-sm text-gray-500 mt-1"><?= count($episodes[0]['server_data']) ?> Tập</p>
                    </div>
                    
                    <div class="flex items-center gap-4 mb-4 border-b border-[#2d2f36] pb-2">
                        <button class="text-[#ff8f00] font-medium text-sm border-b-2 border-[#ff8f00] pb-1">Danh sách phát</button>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-1 max-h-[450px] overflow-y-auto custom-scrollbar pr-1" id="episode-list">
                        <?php foreach ($episodes[0]['server_data'] as $e): ?>
                            <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($e['slug']) ?>" 
                               class="flex items-center justify-between px-3 py-3 rounded hover:bg-[#2d2f36] transition-colors group">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 md:w-16 md:h-10 rounded bg-[#22242d] flex items-center justify-center mr-3 group-hover:text-[#ff8f00] transition-colors text-xs font-medium text-gray-500 overflow-hidden relative">
                                        <img src="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : (!empty($movie['thumb_url']) ? $movie['thumb_url'] : '')) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-30 group-hover:opacity-10 transition-opacity">
                                        <i data-lucide="play" class="w-4 h-4 relative z-10 text-white group-hover:text-[#ff8f00] hidden group-hover:block"></i>
                                    </div>
                                    <span class="text-sm text-gray-300 group-hover:text-[#ff8f00] transition-colors font-medium">Tập <?= htmlspecialchars($e['name']) ?></span>
                                </div>
                                <span class="bg-[#2d2f36] text-[10px] text-gray-400 px-1.5 py-0.5 rounded group-hover:bg-[#ff8f00] group-hover:text-black transition-colors">VIP</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Comments Widget -->
                <div class="bg-[#181a20] rounded-lg p-4 border border-[#2d2f36]">
                    <h3 class="text-base font-bold text-white mb-3">Bình luận (<span id="comment-count">0</span>)</h3>
                    <div class="bg-[#22242d] rounded-md p-3 mb-4">
                        <textarea id="comment-content" rows="2" class="w-full bg-transparent text-white text-sm outline-none resize-none placeholder-gray-500" placeholder="Viết bình luận..."></textarea>
                        <div class="flex items-center justify-end mt-2">
                            <button id="btn-submit-comment" class="bg-[#ff8f00] text-black font-bold px-3 py-1.5 rounded text-xs hover:bg-[#e68000] transition-colors">
                                Gửi
                            </button>
                        </div>
                    </div>
                    <div id="comments-list" class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                        <div class="text-center text-gray-500 text-xs py-4">Đang tải bình luận...</div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Comments logic
    const contentInput = document.getElementById('comment-content');
    const submitBtn = document.getElementById('btn-submit-comment');
    const commentsList = document.getElementById('comments-list');
    const countSpan = document.getElementById('comment-count');
    const movieSlug = '<?= htmlspecialchars($slug) ?>';
    
    function fetchComments() {
        fetch('/api/comments.php?slug=' + movieSlug)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    countSpan.textContent = res.data.length;
                    if (res.data.length === 0) {
                        commentsList.innerHTML = '<div class="text-center text-gray-500 text-xs py-4">Chưa có bình luận.</div>';
                        return;
                    }
                    
                    let html = '';
                    res.data.forEach(c => {
                        html += `
                            <div class="flex gap-3 bg-[#22242d] p-3 rounded-lg border border-[#2d2f36]">
                                <div class="w-8 h-8 rounded-full bg-[#181a20] flex items-center justify-center shrink-0 border border-[#2d2f36]">
                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-baseline gap-2 mb-1">
                                        <span class="font-bold text-gray-200 text-xs">${c.user_name}</span>
                                        <span class="text-[10px] text-gray-500">${c.time_ago}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 leading-relaxed">${c.content}</p>
                                </div>
                            </div>
                        `;
                    });
                    commentsList.innerHTML = html;
                    lucide.createIcons();
                }
            });
    }
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            const content = contentInput.value.trim();
            if (!content) return alert('Vui lòng nhập nội dung bình luận!');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '...';
            
            fetch('/api/comments.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({slug: movieSlug, name: '', content: content, anonymous: true})
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Gửi';
                if (res.success) {
                    contentInput.value = '';
                    fetchComments();
                } else {
                    alert(res.message);
                }
            });
        });
    }
    
    fetchComments();
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
