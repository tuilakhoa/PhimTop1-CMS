<?php include __DIR__ . '/header.php'; 

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://phimimg.com/';
if (!empty($movie['category']) && is_array($movie['category'])) {
    $firstCatObj = reset($movie['category']);
    $firstCat = is_array($firstCatObj) ? ($firstCatObj['slug'] ?? '') : (is_string($firstCatObj) ? $firstCatObj : '');
    if ($firstCat) {
        $sugRes = function_exists('fetchApiWithCache') ? fetchApiWithCache("https://phimapi.com/v1/api/the-loai/" . urlencode($firstCat) . "?limit=12", 3600) : @file_get_contents("https://phimapi.com/v1/api/the-loai/" . urlencode($firstCat) . "?limit=12");
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
    $tmdbRes = function_exists('fetchApiWithCache') ? fetchApiWithCache("https://api.themoviedb.org/3/{$tmdbType}/{$tmdbId}/images?api_key=" . urlencode($tmdbApiKey), 86400) : @file_get_contents("https://api.themoviedb.org/3/{$tmdbType}/{$tmdbId}/images?api_key=" . urlencode($tmdbApiKey));
    if ($tmdbRes) {
        $tmdbData = json_decode($tmdbRes, true);
        if (isset($tmdbData['backdrops'])) $movieImages['backdrops'] = $tmdbData['backdrops'];
        if (isset($tmdbData['posters'])) $movieImages['posters'] = $tmdbData['posters'];
    }
} else {
    // Fallback to PhimAPI
    $imgRes = function_exists('fetchApiWithCache') ? fetchApiWithCache("https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/images", 86400) : @file_get_contents("https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/images");
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

// Auto redirect for watch party
if (!empty($_GET['party'])) {
    $partyCode = strtoupper(trim($_GET['party']));
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT episode_name FROM watch_parties WHERE room_code = ? AND status = 'active'");
        $stmt->execute([$partyCode]);
        $partyRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($partyRow) {
            $epName = $partyRow['episode_name'];
            $targetEpSlug = '';
            if (!empty($episodes[0]['server_data'])) {
                foreach ($episodes[0]['server_data'] as $e) {
                    if ($e['name'] == $epName) {
                        $targetEpSlug = $e['slug'];
                        break;
                    }
                }
            }
            if ($targetEpSlug) {
                $watchUrl = '/' . ($settings["slugWatch"] ?? "xem-phim") . '/' . urlencode($slug) . '/' . urlencode($targetEpSlug) . '?party=' . urlencode($partyCode);
                header("Location: $watchUrl");
                exit;
            }
        }
    }
}
?>

<!-- New Cinematic Hero Section -->
<div class="relative w-full flex items-end -mt-20 overflow-hidden min-h-[70vh] pb-8 md:pb-12 pt-28">
    <!-- Background Layer (Optimized with fetchpriority) -->
    <div class="absolute inset-0 z-0 bg-black">
        <img fetchpriority="high" src="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : $movie['thumb_url']) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-full object-cover opacity-30">
        <!-- Gradients to blend -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/80 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0a] via-[#0a0a0a]/50 to-transparent"></div>
    </div>

    <!-- Hero Content Layer -->
    <div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1200px] relative z-10 flex flex-col md:flex-row items-center md:items-end gap-6 md:gap-8">
        <!-- Poster -->
        <div class="shrink-0 rounded-xl overflow-hidden shadow-2xl border border-gray-800 bg-[#141414] w-40 md:w-60">
            <img fetchpriority="high" src="<?= htmlspecialchars($movie['thumb_url']) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-auto aspect-[3/4] object-cover">
        </div>

        <!-- Main Info -->
        <div class="flex-1 min-w-0 flex flex-col gap-4 items-center text-center md:items-start md:text-left w-full">
            <h1 class="text-3xl md:text-5xl font-bold text-white leading-tight drop-shadow-lg"><?= htmlspecialchars($movie['name']) ?></h1>
            <h2 class="text-lg md:text-xl text-gray-400 italic"><?= htmlspecialchars($movie['origin_name']) ?></h2>

            <!-- Fast Horizontal Metadata (No heavy blur) -->
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 text-sm text-gray-200 mt-2">
                <span class="bg-[#fcc526] text-black font-bold px-2.5 py-1 rounded shadow"><?= htmlspecialchars($movie['quality'] ?? 'HD') ?></span>
                <span class="bg-gray-800 border border-gray-700 px-2.5 py-1 rounded"><?= htmlspecialchars($movie['lang'] ?? 'Vietsub') ?></span>
                <span class="bg-gray-800 border border-gray-700 px-2.5 py-1 rounded"><?= htmlspecialchars($movie['year'] ?? date('Y')) ?></span>
                <span class="bg-gray-800 border border-gray-700 px-2.5 py-1 rounded flex items-center"><i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5"></i><?= htmlspecialchars($movie['time'] ?? 'Đang cập nhật') ?></span>
                <span class="bg-gray-800 border border-gray-700 px-2.5 py-1 rounded text-[#fcc526] flex items-center"><i data-lucide="play-circle" class="w-3.5 h-3.5 mr-1.5"></i><?= htmlspecialchars($movie['episode_current'] ?? 'Đang cập nhật') ?></span>
                <?php if ($tmdbVote > 0): ?>
                <span class="bg-blue-900 border border-blue-800 px-2.5 py-1 rounded font-bold flex items-center text-white"><i data-lucide="star" class="w-3.5 h-3.5 mr-1.5 text-[#fcc526] fill-current"></i><?= number_format($tmdbVote, 1) ?></span>
                <?php endif; ?>
            </div>

            <!-- Genres -->
            <div class="flex flex-wrap gap-2 mt-1">
                <?php 
                $cats = is_array($movie['category'] ?? null) ? $movie['category'] : [];
                foreach ($cats as $cat): 
                    $catName = is_array($cat) ? ($cat['name'] ?? '') : $cat;
                ?>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors text-sm font-medium">#<?= htmlspecialchars($catName) ?></a>
                <?php endforeach; ?>
            </div>

            <!-- Actions (Solid colors for performance) -->
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-5 w-full">
                <?php if (!empty($episodes[0]['server_data'])): ?>
                    <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($episodes[0]['server_data'][0]['slug']) ?>" 
                       class="px-8 py-3 bg-[#fcc526] hover:bg-yellow-500 text-black font-bold rounded-lg transition-transform hover:-translate-y-1 flex items-center justify-center shadow-lg w-full md:w-auto">
                        <i data-lucide="play" class="w-5 h-5 fill-current mr-2"></i> Xem ngay
                    </a>
                <?php else: ?>
                    <button class="px-8 py-3 bg-gray-800 text-gray-500 font-bold rounded-lg cursor-not-allowed border border-gray-700 w-full md:w-auto">
                        Đang Cập Nhật
                    </button>
                <?php endif; ?>
                
                <button onclick="toggleFollowMovie()" id="follow-btn" class="px-5 py-3 bg-gray-800 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center border border-gray-700 shadow-lg w-full sm:flex-1 md:w-auto md:flex-none">
                    <i data-lucide="bookmark" id="follow-icon" class="w-5 h-5 mr-2"></i> <span id="follow-text">Lưu phim</span>
                </button>
                
                <button onclick="shareMovie('<?= htmlspecialchars(addslashes($movie['name'])) ?>')" class="px-4 py-3 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center border border-gray-700 shadow-lg w-full sm:w-auto" title="Chia sẻ">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Details Section -->
<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1200px] mb-16 mt-6">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Left Column (Core content: Episodes, Plot, Actors, Comments) -->
        <div class="flex-1 min-w-0 space-y-10">
            
            <!-- Episodes Section -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <span class="w-1.5 h-6 bg-[#fcc526] mr-3 rounded-full shadow-[0_0_8px_#fcc526]"></span> Danh Sách Tập
                    </h3>
                </div>
                
                <div class="bg-[#141414] rounded-2xl p-5 border border-gray-800 shadow-inner">
                    <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-8 gap-3 max-h-[350px] overflow-y-auto custom-scrollbar" id="episode-list">
                        <?php 
                        $server = $episodes[0] ?? ['server_data' => []];
                        foreach ($server['server_data'] as $ep): 
                        ?>
                            <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($ep['slug']) ?>" 
                               class="px-2 py-2.5 bg-[#202020] hover:bg-[#fcc526] hover:text-black text-gray-300 text-sm font-medium rounded-lg transition-colors text-center truncate border border-gray-800"
                               title="<?= htmlspecialchars($ep['name']) ?>">
                                <?= htmlspecialchars($ep['name']) ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if(empty($server['server_data'])): ?>
                            <div class="col-span-full text-gray-500 text-sm py-4">Chưa có tập phim nào.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Content/Plot -->
            <div>
                <h3 class="text-xl font-bold text-white flex items-center mb-4">
                    <span class="w-1.5 h-6 bg-[#fcc526] mr-3 rounded-full shadow-[0_0_8px_#fcc526]"></span> Nội Dung Phim
                </h3>
                <div class="text-gray-300 text-[15px] leading-relaxed whitespace-pre-line text-justify bg-[#141414] p-6 rounded-2xl border border-gray-800">
                    <?= nl2br(htmlspecialchars(strip_tags($movie['content'] ?? 'Chưa có nội dung mô tả cho phim này.'))) ?>
                </div>
            </div>

            <!-- Cast / Peoples Component -->
            <?php include __DIR__ . '/components/actors.php'; ?>

            <!-- Comments -->
            <div id="comments-section" data-slug="<?= htmlspecialchars($slug) ?>">
                <h3 class="text-xl font-bold text-white flex items-center mb-4">
                    <span class="w-1.5 h-6 bg-[#fcc526] mr-3 rounded-full shadow-[0_0_8px_#fcc526]"></span> Bình luận (<span id="comment-count">0</span>)
                </h3>
                
                <div class="bg-[#141414] rounded-2xl p-6 border border-gray-800">
                    <div class="bg-[#202020] rounded-xl p-4 border border-gray-700 focus-within:border-gray-500 transition-colors shadow-inner">
                        <input type="text" id="comment-name" class="w-full bg-transparent text-white text-sm outline-none mb-3 pb-3 border-b border-gray-800 hidden" placeholder="Nhập tên của bạn...">
                        <textarea id="comment-content" rows="2" class="w-full bg-transparent text-white text-sm outline-none resize-none placeholder-gray-500" placeholder="Chia sẻ cảm nghĩ của bạn về bộ phim này..."></textarea>
                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-800">
                            <label class="flex items-center text-gray-400 text-sm cursor-pointer hover:text-white transition-colors select-none">
                                <input type="checkbox" id="comment-anon" checked class="mr-2 rounded border-gray-600 bg-gray-700 text-[#fcc526] focus:ring-[#fcc526]"> Ẩn danh
                            </label>
                            <button id="btn-submit-comment" class="bg-white hover:bg-gray-200 text-black font-bold px-5 py-2 rounded-lg text-sm flex items-center transition-colors shadow">
                                Gửi <i data-lucide="send" class="w-4 h-4 ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="comments-list" class="mt-8 space-y-6">
                        <div class="text-center text-gray-500 text-sm py-4">Đang tải bình luận...</div>
                    </div>
                </div>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var anonCheckbox = document.getElementById('comment-anon');
                var nameInput = document.getElementById('comment-name');
                var contentInput = document.getElementById('comment-content');
                var submitBtn = document.getElementById('btn-submit-comment');
                var commentsList = document.getElementById('comments-list');
                var countSpan = document.getElementById('comment-count');
                var movieSlug = '<?= htmlspecialchars($slug) ?>';
                var currentUser = <?= json_encode($_SESSION['user']['name'] ?? '') ?>;
                var isAdmin = <?= isset($_SESSION['admin']) ? 'true' : 'false' ?>;
                
                anonCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        nameInput.classList.add('hidden');
                    } else {
                        nameInput.classList.remove('hidden');
                        nameInput.focus();
                    }
                });
                
                window.deleteComment = function(id) {
                    if(confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
                        fetch('/api/comments.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({action: 'delete', id: id})
                        })
                        .then(res => res.json())
                        .then(res => {
                            if(res.success) fetchComments();
                            else alert(res.message);
                        });
                    }
                };
                
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
                                
                                var html = '';
                                res.data.forEach(c => {
                                    var deleteBtn = '';
                                    if (isAdmin || (currentUser && currentUser === c.user_name)) {
                                        deleteBtn = `<button onclick="deleteComment(${c.id})" class="text-red-500 text-xs ml-3 hover:underline font-medium">Xóa</button>`;
                                    }
                                    
                                    html += `
                                        <div class="flex gap-4">
                                            <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center shrink-0 border border-gray-700">
                                                <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                            </div>
                                            <div class="flex-1 bg-[#202020] p-4 rounded-xl rounded-tl-none border border-gray-800/50">
                                                <div class="flex items-baseline mb-2 border-b border-gray-800 pb-2">
                                                    <span class="font-bold text-gray-200 text-sm mr-2">${c.user_name}</span>
                                                    <span class="text-xs text-gray-500">${c.time_ago}</span>
                                                    ${deleteBtn}
                                                </div>
                                                <p class="text-sm text-gray-300 leading-relaxed">${c.content}</p>
                                            </div>
                                        </div>
                                    `;
                                });
                                commentsList.innerHTML = html;
                                if(typeof lucide !== 'undefined') lucide.createIcons();
                            }
                        });
                }
                
                submitBtn.addEventListener('click', function() {
                    var content = contentInput.value.trim();
                    var isAnon = anonCheckbox.checked;
                    var name = isAnon ? '' : nameInput.value.trim();
                    
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
                        submitBtn.innerHTML = 'Gửi <i data-lucide="send" class="w-4 h-4 ml-2"></i>';
                        if(typeof lucide !== 'undefined') lucide.createIcons();
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

        <!-- Right Column: Meta Info (Directors, Country) -->
        <div class="w-full md:w-[280px] shrink-0">
            <div class="bg-[#141414] rounded-2xl p-6 border border-gray-800 sticky top-24">
                <h3 class="text-lg font-bold text-white mb-5 border-b border-gray-800 pb-3 flex items-center">
                    <i data-lucide="info" class="w-5 h-5 mr-2 text-gray-400"></i> Thông tin thêm
                </h3>
                
                <div class="space-y-4 text-sm">
                    <div class="flex flex-col">
                        <span class="text-gray-500 mb-1">Đạo diễn:</span>
                        <span class="text-gray-200 font-medium"><?= htmlspecialchars(is_array($movie['director'] ?? null) ? implode(', ', $movie['director']) : ($movie['director'] ?? 'Đang cập nhật')) ?></span>
                    </div>
                    
                    <div class="flex flex-col">
                        <span class="text-gray-500 mb-1">Quốc gia:</span>
                        <span class="text-gray-200 font-medium"><?= htmlspecialchars(is_array($movie['country'] ?? null) ? implode(', ', array_map(function($c) { return is_array($c) ? $c['name'] : $c; }, $movie['country'])) : ($movie['country'] ?? 'Đang cập nhật')) ?></span>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-gray-500 mb-1">Tổng số tập:</span>
                        <span class="text-gray-200 font-medium"><?= htmlspecialchars($movie['episode_total'] ?? 'Đang cập nhật') ?></span>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-gray-500 mb-1">Chất lượng:</span>
                        <span class="text-[#fcc526] font-medium"><?= htmlspecialchars($movie['quality'] ?? 'HD') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Movie Suggestions (Lazy loaded images) -->
<?php if (!empty($suggestions)): ?>
<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1200px] mb-16 relative z-10">
    <h3 class="text-2xl font-bold mb-6 flex items-center text-white">
        <span class="w-1.5 h-6 bg-red-500 mr-3 rounded-full shadow-[0_0_8px_#ef4444]"></span> Đề Xuất Phim Liên Quan
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        <?php foreach ($suggestions as $item): ?>
            <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group block relative overflow-hidden rounded-xl bg-[#141414] transition-all duration-300">
                <div class="aspect-[3/4] relative overflow-hidden rounded-xl border border-gray-800 group-hover:border-gray-600 transition-colors">
                    <img loading="lazy" src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    
                    <!-- Vietsub Badge -->
                    <div class="absolute top-2 right-2">
                        <span class="bg-[#fcc526] text-black text-[10px] font-bold px-2 py-0.5 rounded shadow uppercase">
                            <?= htmlspecialchars($item['lang'] ?? 'Vietsub') ?>
                        </span>
                    </div>
                    
                    <!-- Play icon -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="w-12 h-12 bg-black/60 rounded-full flex items-center justify-center border border-gray-500 backdrop-blur-sm transform group-hover:scale-110 transition-all">
                            <i data-lucide="play" class="w-5 h-5 text-white fill-current ml-1"></i>
                        </div>
                    </div>
                </div>
                <div class="pt-3 pb-2 px-1">
                    <h3 class="text-white font-medium text-sm truncate group-hover:text-[#fcc526] transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-xs text-gray-500 truncate mt-0.5"><?= htmlspecialchars($item['origin_name']) ?> <span class="text-gray-600 ml-1">(<?= htmlspecialchars($item['year'] ?? date('Y')) ?>)</span></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
    const movieData = {
        slug: "<?= addslashes($slug) ?>",
        name: "<?= addslashes(htmlspecialchars_decode($movie['name'])) ?>",
        thumb_url: "<?= addslashes($movie['thumb_url']) ?>"
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Check initial follow state
        fetch(`/api/follow.php?action=check&slug=${encodeURIComponent(movieData.slug)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.is_following) {
                    updateFollowBtnUI(true);
                }
            })
            .catch(err => console.error("Error checking follow status:", err));
    });

    function updateFollowBtnUI(isFollowing) {
        const icon = document.getElementById('follow-icon');
        const text = document.getElementById('follow-text');
        if (!icon || !text) return;
        
        if (isFollowing) {
            icon.classList.add('fill-current', 'text-[#fcc526]');
            text.innerText = 'Đã lưu';
        } else {
            icon.classList.remove('fill-current', 'text-[#fcc526]');
            text.innerText = 'Lưu phim';
        }
    }

    function toggleFollowMovie() {
        fetch('/api/follow.php?action=toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                item_slug: movieData.slug,
                item_type: 'movie',
                item_name: movieData.name,
                thumb_url: movieData.thumb_url
            })
        })
        .then(res => {
            if (res.status === 401) {
                alert("Vui lòng đăng nhập để lưu phim!");
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (data && data.status === 'success') {
                updateFollowBtnUI(data.action === 'added');
            } else if (data && data.message) {
                alert("Lỗi: " + data.message);
            }
        })
        .catch(err => console.error("Error toggling follow:", err));
    }
</script>
<?php include __DIR__ . '/footer.php'; ?>
