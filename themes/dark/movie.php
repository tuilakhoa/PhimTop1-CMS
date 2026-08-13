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

<div class="container mx-auto px-4 py-8">
    <!-- Movie Details Header -->
    <div class="relative w-full rounded-2xl overflow-hidden mb-12 shadow-2xl bg-gray-900">
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : (!empty($movie['thumb_url']) ? $movie['thumb_url'] : '')) ?>" 
                 alt="Poster" class="w-full h-full object-cover opacity-30 blur-sm">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-900/80 to-transparent"></div>
        </div>
        
        <div class="relative z-10 p-6 md:p-12 flex flex-col md:flex-row gap-8">
            <div class="flex-shrink-0 w-48 md:w-64 mx-auto md:mx-0">
                <img src="<?= htmlspecialchars(!empty($movie['thumb_url']) ? $movie['thumb_url'] : (!empty($movie['poster_url']) ? $movie['poster_url'] : '')) ?>" 
                     alt="Thumb" class="w-full rounded-xl shadow-2xl border-2 border-gray-700/50">
            </div>
            
            <div class="flex-grow min-w-0">
                <div class="flex flex-wrap gap-2 mb-4">
                    <?php if (!empty($movie['quality'])): ?>
                        <span class="inline-flex items-center whitespace-nowrap w-fit px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-md"><?= htmlspecialchars($movie['quality']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($movie['lang'])): ?>
                        <span class="inline-flex items-center whitespace-nowrap w-fit px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-md"><?= htmlspecialchars($movie['lang']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($movie['year'])): ?>
                        <span class="inline-flex items-center whitespace-nowrap w-fit px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-md"><?= htmlspecialchars($movie['year']) ?></span>
                    <?php endif; ?>
                </div>
                
                <h1 class="text-3xl md:text-5xl font-bold text-white mb-2"><?= htmlspecialchars($movie['name']) ?></h1>
                <p class="text-xl text-gray-400 mb-4 italic"><?= htmlspecialchars($movie['origin_name'] ?? '') ?></p>
                
                <?php if ($tmdbVote > 0): ?>
                <div class="flex items-center gap-2 mb-6">
                    <span class="inline-flex items-center text-yellow-500 font-bold bg-yellow-500/10 px-2 py-1 rounded">
                        <i data-lucide="star" class="w-4 h-4 mr-1 fill-current"></i> <?= number_format($tmdbVote, 1) ?>
                    </span>
                    <span class="text-gray-400 text-sm">(<?= number_format($tmdbCount) ?> votes)</span>
                </div>
                <?php else: ?>
                <div class="mb-6"></div>
                <?php endif; ?>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 text-sm">
                    <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                        <span class="text-gray-500 block mb-1">Trạng thái</span>
                        <span class="text-white font-semibold"><?= htmlspecialchars($movie['episode_current'] ?? 'N/A') ?></span>
                    </div>
                    <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                        <span class="text-gray-500 block mb-1">Thời lượng</span>
                        <span class="text-white font-semibold"><?= htmlspecialchars($movie['time'] ?? 'N/A') ?></span>
                    </div>
                    <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                        <span class="text-gray-500 block mb-1">Loại</span>
                        <span class="text-white font-semibold"><?= htmlspecialchars($movie['type'] ?? 'N/A') ?></span>
                    </div>
                </div>
                
                <div class="mb-8 text-gray-300 leading-relaxed break-words">
                    <?= !empty($movie['content']) ? strip_tags($movie['content'], '<p><br><b><i>') : 'Chưa có tóm tắt.' ?>
                </div>

                <!-- Cast / Peoples Component -->
                <?php include __DIR__ . '/components/actors.php'; ?>

                <!-- Image Gallery -->
                <?php if (!empty($movieImages['backdrops']) || !empty($movieImages['posters'])): ?>
                <div class="mb-8">
                    <h3 class="text-xl font-bold mb-3 text-white border-l-4 border-red-500 pl-2">Hình Ảnh Phim</h3>
                    <div class="flex overflow-x-auto gap-4 custom-scrollbar pb-4 snap-x">
                        <?php 
                        $bCount = 0;
                        foreach ($movieImages['backdrops'] as $img): 
                            if ($bCount++ >= 10) break;
                        ?>
                            <div class="shrink-0 w-[240px] md:w-[280px] rounded-xl overflow-hidden border border-gray-700 snap-start">
                                <img src="https://image.tmdb.org/t/p/w780<?= htmlspecialchars($img['file_path']) ?>" alt="Backdrop" loading="lazy" class="w-full h-[135px] md:h-[157px] object-cover hover:scale-110 transition-transform duration-500">
                            </div>
                        <?php endforeach; ?>
                        
                        <?php 
                        $pCount = 0;
                        foreach ($movieImages['posters'] as $img): 
                            if ($pCount++ >= 5) break;
                        ?>
                            <div class="shrink-0 w-[100px] md:w-[120px] rounded-xl overflow-hidden border border-gray-700 snap-start">
                                <img src="https://image.tmdb.org/t/p/w342<?= htmlspecialchars($img['file_path']) ?>" alt="Poster" loading="lazy" class="w-full aspect-[2/3] object-cover hover:scale-110 transition-transform duration-500">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($episodes) && !empty($episodes[0]['server_data'])): ?>
                    <div class="flex flex-wrap gap-4">
                        <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($episodes[0]['server_data'][0]['slug']) ?>" 
                           class="inline-flex items-center justify-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3.5 rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg shadow-red-600/30">
                            <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                            <span>Xem Ngay</span>
                        </a>
                        <button id="btn-follow-movie" class="hidden items-center justify-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white px-8 py-3.5 rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg border border-gray-700">
                            <i data-lucide="bookmark" id="icon-follow-movie" class="w-5 h-5"></i>
                            <span id="text-follow-movie">Theo dõi</span>
                        </button>
                        <button id="btn-playlist-movie" class="hidden items-center justify-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white px-8 py-3.5 rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg border border-gray-700">
                            <i data-lucide="list-plus" class="w-5 h-5"></i>
                            <span>Danh sách phát</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Episode List (Below Details) -->
    <?php if (!empty($episodes[0]['server_data'])): ?>
        <div class="mb-12 bg-gray-900 rounded-2xl p-6 border border-gray-800">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                <i data-lucide="list-video" class="w-5 h-5 mr-2 text-red-500"></i> Chọn tập phim
            </h3>
            <div class="flex flex-wrap gap-2 p-2">
                <?php foreach ($episodes[0]['server_data'] as $e): ?>
                    <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($e['slug']) ?>" 
                       class="px-4 py-2 rounded-lg transition-all bg-gray-800 text-gray-300 hover:bg-red-600 hover:text-white hover:shadow-lg hover:shadow-red-600/30 font-medium">
                        <?= htmlspecialchars($e['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Comments (Dynamic UI) -->
    <div class="mb-12 bg-gray-900 rounded-2xl p-6 border border-gray-800">
        <h3 class="text-lg font-bold text-white mb-6 flex items-center">
            <i data-lucide="message-square" class="w-5 h-5 mr-2 text-red-500"></i> Bình luận (<span id="comment-count">0</span>)
        </h3>
        
        <div class="relative bg-gray-800 rounded-xl p-4 border border-gray-700">
            <input type="text" id="comment-name" class="w-full bg-transparent text-white text-sm outline-none mb-3 pb-2 border-b border-gray-700 hidden" placeholder="Nhập tên của bạn...">
            <textarea id="comment-content" rows="3" class="w-full bg-transparent text-white text-sm outline-none resize-none placeholder-gray-400" placeholder="Vui lòng nhập nội dung bình luận..."></textarea>
            <div class="flex items-center justify-between mt-3 border-t border-gray-700 pt-4">
                <label class="flex items-center text-gray-400 text-sm cursor-pointer hover:text-white transition-colors">
                    <input type="checkbox" id="comment-anon" checked class="mr-2 rounded border-gray-600 bg-gray-700 text-red-500 focus:ring-red-500"> Ẩn danh ?
                </label>
                <button id="btn-submit-comment" class="bg-red-600 text-white font-bold px-5 py-2 rounded-lg text-sm flex items-center hover:bg-red-700 transition-colors shadow-lg shadow-red-600/20">
                    Gửi bình luận <i data-lucide="send" class="w-4 h-4 ml-2"></i>
                </button>
            </div>
        </div>
        
        <div id="comments-list" class="mt-8 space-y-6">
            <div class="text-center text-gray-500 text-sm py-4">Đang tải bình luận...</div>
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
        
        if (anonCheckbox) {
            anonCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    nameInput.classList.add('hidden');
                } else {
                    nameInput.classList.remove('hidden');
                    nameInput.focus();
                }
            });
        }
        
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
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center shrink-0 border border-gray-700">
                                        <svg class="w-6 h-6 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div class="flex-1 bg-gray-800/50 p-4 rounded-xl border border-gray-700/50">
                                        <div class="flex items-baseline gap-3 mb-2">
                                            <span class="font-bold text-gray-100">${c.user_name}</span>
                                            <span class="text-xs text-gray-500">${c.time_ago}</span>
                                        </div>
                                        <p class="text-sm text-gray-300 leading-relaxed">${c.content}</p>
                                    </div>
                                </div>
                            `;
                        });
                        commentsList.innerHTML = html;
                    }
                });
        }
        
        if (submitBtn) {
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
                    submitBtn.innerHTML = 'Gửi bình luận <svg class="w-4 h-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
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
        
        // Follow logic
        const btnFollow = document.getElementById('btn-follow-movie');
        if (btnFollow) {
            const iconFollow = document.getElementById('icon-follow-movie');
            const textFollow = document.getElementById('text-follow-movie');
            
            // Check follow status
            fetch('/api/follow.php?action=check&slug=' + movieSlug)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        btnFollow.classList.remove('hidden');
                        btnFollow.classList.add('inline-flex');
                        if (res.is_following) {
                            textFollow.textContent = 'Hủy theo dõi';
                            iconFollow.classList.add('fill-current', 'text-red-500');
                        }
                    } else if (res.status === 'error' && res.message === 'Unauthorized') {
                        // Show button but redirect to login on click
                        btnFollow.classList.remove('hidden');
                        btnFollow.classList.add('inline-flex');
                    }
                });
                
            btnFollow.addEventListener('click', function() {
                const thumbUrl = '<?= htmlspecialchars(!empty($movie['thumb_url']) ? $movie['thumb_url'] : (!empty($movie['poster_url']) ? $movie['poster_url'] : '')) ?>';
                const name = '<?= htmlspecialchars($movie['name']) ?>';
                
                fetch('/api/follow.php?action=toggle', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        item_slug: movieSlug,
                        item_type: 'movie',
                        item_name: name,
                        thumb_url: thumbUrl
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        if (res.action === 'added') {
                            textFollow.textContent = 'Hủy theo dõi';
                            iconFollow.classList.add('fill-current', 'text-red-500');
                        } else {
                            textFollow.textContent = 'Theo dõi';
                            iconFollow.classList.remove('fill-current', 'text-red-500');
                        }
                    } else if (res.status === 'error' && res.message === 'Unauthorized') {
                        window.location.href = '/member.php?mode=login&error=' + encodeURIComponent('Vui lòng đăng nhập để theo dõi.');
                    }
                });
            });
        }
        
        // Playlist logic
        const btnPlaylist = document.getElementById('btn-playlist-movie');
        const modalPlaylist = document.getElementById('modal-playlist');
        const closePlaylist = document.getElementById('close-playlist-modal');
        const listPlaylist = document.getElementById('list-playlist');
        const newPlaylistInput = document.getElementById('new-playlist-name');
        const btnCreatePlaylist = document.getElementById('btn-create-playlist');
        
        if (btnPlaylist) {
            // Show button if logged in (check via follow api check is a trick, but let's just show it if res.message !== Unauthorized)
            fetch('/api/playlists.php?action=check&slug=' + movieSlug)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' || (res.status === 'error' && res.message === 'Unauthorized')) {
                        btnPlaylist.classList.remove('hidden');
                        btnPlaylist.classList.add('inline-flex');
                    }
                });
                
            function openPlaylistModal() {
                fetch('/api/playlists.php?action=check&slug=' + movieSlug)
                .then(res => res.json())
                .then(checkRes => {
                    if (checkRes.status === 'error' && checkRes.message === 'Unauthorized') {
                        window.location.href = '/member.php?mode=login&error=' + encodeURIComponent('Vui lòng đăng nhập để dùng danh sách phát.');
                        return;
                    }
                    
                    const inPlaylists = checkRes.in_playlists || [];
                    
                    fetch('/api/playlists.php?action=list')
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            let html = '';
                            if (res.data.length === 0) {
                                html = '<div class="text-center text-gray-500 text-sm py-4">Bạn chưa có danh sách phát nào.</div>';
                            } else {
                                res.data.forEach(pl => {
                                    const inPl = inPlaylists.includes(pl.id);
                                    html += `
                                        <div class="flex items-center justify-between bg-gray-700/50 p-3 rounded-lg border border-gray-600">
                                            <span class="text-white font-medium">${pl.name}</span>
                                            ${inPl 
                                                ? `<span class="text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded">Đã thêm</span>`
                                                : `<button onclick="addToPlaylist(${pl.id})" class="text-xs bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded transition-colors">Thêm</button>`
                                            }
                                        </div>
                                    `;
                                });
                            }
                            listPlaylist.innerHTML = html;
                            modalPlaylist.classList.remove('hidden');
                            modalPlaylist.classList.add('flex');
                        }
                    });
                });
            }
            
            window.addToPlaylist = function(id) {
                const thumbUrl = '<?= htmlspecialchars(!empty($movie['thumb_url']) ? $movie['thumb_url'] : (!empty($movie['poster_url']) ? $movie['poster_url'] : '')) ?>';
                const name = '<?= htmlspecialchars($movie['name']) ?>';
                
                fetch('/api/playlists.php?action=add_item', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        playlist_id: id,
                        movie_slug: movieSlug,
                        movie_name: name,
                        thumb_url: thumbUrl
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        openPlaylistModal(); // Refresh list
                    } else {
                        alert(res.message);
                    }
                });
            };
            
            btnPlaylist.addEventListener('click', openPlaylistModal);
            
            closePlaylist.addEventListener('click', () => {
                modalPlaylist.classList.add('hidden');
                modalPlaylist.classList.remove('flex');
            });
            
            btnCreatePlaylist.addEventListener('click', () => {
                const name = newPlaylistInput.value.trim();
                if (!name) return alert('Vui lòng nhập tên danh sách phát');
                
                fetch('/api/playlists.php?action=create', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ name: name })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        newPlaylistInput.value = '';
                        openPlaylistModal();
                    } else {
                        alert(res.message);
                    }
                });
            });
        }
    });
    </script>
    
    <!-- Modal Playlist -->
    <div id="modal-playlist" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/80 backdrop-blur-sm px-4">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
            <div class="flex justify-between items-center p-4 border-b border-gray-700">
                <h3 class="text-lg font-bold text-white flex items-center"><i data-lucide="list" class="w-5 h-5 mr-2 text-red-500"></i> Lưu vào danh sách phát</h3>
                <button id="close-playlist-modal" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-4 max-h-[60vh] overflow-y-auto custom-scrollbar space-y-3" id="list-playlist">
                <!-- Playlists will be loaded here -->
            </div>
            <div class="p-4 border-t border-gray-700 bg-gray-800/80">
                <div class="flex gap-2">
                    <input type="text" id="new-playlist-name" placeholder="Tên danh sách mới..." class="flex-1 bg-gray-900 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm outline-none focus:border-red-500 transition-colors">
                    <button id="btn-create-playlist" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-lg shadow-red-600/20 whitespace-nowrap">Tạo mới</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Movie Suggestions -->
    <?php if (!empty($suggestions)): ?>
    <div class="mb-12">
        <h3 class="text-2xl font-bold text-white mb-6 border-l-4 border-red-500 pl-3">Có Thể Bạn Sẽ Thích</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach ($suggestions as $item): ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-800 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-500/20">
                    <div class="relative aspect-[2/3] w-full overflow-hidden">
                        <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute top-2 left-2">
                            <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($item['quality'] ?? 'HD') ?></span>
                        </div>
                    </div>
                    <div class="p-3 relative z-10 flex flex-col flex-grow">
                        <h3 class="text-sm font-semibold text-white line-clamp-1 mb-1 group-hover:text-red-400 transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
