<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thành Viên - <?= htmlspecialchars($settings['siteName']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #3f3f46; border-radius: 20px; }
    </style>
</head>
<body class="bg-zinc-950 min-h-screen relative overflow-x-hidden custom-scrollbar">
    <!-- Hiệu ứng Background (Dark Theme: Red & Dark) -->
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-red-600/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-zinc-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="flex items-center justify-center min-h-screen w-full p-4 relative z-10">
        
        <div class="max-w-md w-full bg-zinc-900/80 backdrop-blur-xl border border-zinc-800/50 rounded-3xl shadow-2xl overflow-hidden">
            <?php if ($isLoggedIn): ?>
                <div class="p-8">
                    <div class="flex items-center space-x-4 mb-8 border-b border-zinc-800/50 pb-6">
                        <img src="<?= htmlspecialchars($user['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($user['name'])) ?>" alt="Avatar" class="w-16 h-16 rounded-full border-2 border-red-500">
                        <div>
                            <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-zinc-400"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="ml-auto">
                            <a href="/api/auth.php?action=logout" class="bg-red-500/10 text-red-500 hover:bg-red-500/20 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                                <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Thoát
                            </a>
                        </div>
                    </div>
                    
                    <!-- Tabs -->
                    <div class="flex gap-4 mb-6 border-b border-zinc-800/50">
                        <button class="tab-btn active text-white font-bold pb-2 border-b-2 border-red-500" data-target="tab-follows">Đang theo dõi</button>
                        <button class="tab-btn text-zinc-400 font-bold pb-2 border-b-2 border-transparent hover:text-white" data-target="tab-playlists">Danh sách phát</button>
                    </div>

                    <!-- Follows Tab -->
                    <div id="tab-follows" class="tab-content block">
                        <?php if (empty($follows)): ?>
                            <div class="text-center py-8 text-zinc-400 bg-zinc-800/30 rounded-xl border border-zinc-800/50">
                                <i data-lucide="film" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p>Bạn chưa theo dõi nội dung nào.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                <?php foreach ($follows as $item): ?>
                                    <a href="<?= $item['item_type'] === 'comic' ? '/truyen-tranh/'.$item['item_slug'] : '/phim/'.$item['item_slug'] ?>" class="block group">
                                        <div class="relative aspect-[2/3] rounded-xl overflow-hidden mb-2 bg-zinc-800">
                                            <img src="<?= htmlspecialchars($item['thumb_url']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                            <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md text-xs px-2 py-1 rounded text-white font-medium">
                                                <?= $item['item_type'] === 'comic' ? 'Truyện' : 'Phim' ?>
                                            </div>
                                        </div>
                                        <h4 class="text-white font-medium text-sm line-clamp-1 group-hover:text-red-400 transition-colors"><?= htmlspecialchars($item['item_name']) ?></h4>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Playlists Tab -->
                    <div id="tab-playlists" class="tab-content hidden space-y-6">
                        <?php if (empty($playlists)): ?>
                            <div class="text-center py-8 text-zinc-400 bg-zinc-800/30 rounded-xl border border-zinc-800/50">
                                <i data-lucide="list" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p>Bạn chưa có danh sách phát nào.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($playlists as $pl): ?>
                                <div class="bg-zinc-800/50 rounded-xl border border-zinc-800/50 p-4">
                                    <div class="flex justify-between items-center mb-4 border-b border-zinc-700/50 pb-2">
                                        <h4 class="text-lg font-bold text-white flex items-center">
                                            <i data-lucide="list-video" class="w-5 h-5 mr-2 text-red-500"></i> <?= htmlspecialchars($pl['name']) ?>
                                        </h4>
                                        <button onclick="deletePlaylist(<?= $pl['id'] ?>)" class="text-xs text-red-500 hover:text-red-400 font-medium transition-colors">Xóa danh sách</button>
                                    </div>
                                    
                                    <?php if (empty($pl['items'])): ?>
                                        <p class="text-sm text-zinc-500 italic">Chưa có phim nào trong danh sách.</p>
                                    <?php else: ?>
                                        <div class="flex overflow-x-auto gap-4 custom-scrollbar pb-2">
                                            <?php foreach ($pl['items'] as $item): ?>
                                                <div class="shrink-0 w-[120px] relative group">
                                                    <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($item['movie_slug']) ?>" class="block">
                                                        <div class="relative aspect-[2/3] rounded-lg overflow-hidden mb-2 bg-zinc-800 border border-zinc-700">
                                                            <img src="<?= htmlspecialchars($item['thumb_url']) ?>" alt="<?= htmlspecialchars($item['movie_name']) ?>" class="w-full h-full object-cover">
                                                        </div>
                                                        <h5 class="text-zinc-300 font-medium text-xs line-clamp-1 group-hover:text-white transition-colors"><?= htmlspecialchars($item['movie_name']) ?></h5>
                                                    </a>
                                                    <button onclick="removePlaylistItem(<?= $pl['id'] ?>, '<?= htmlspecialchars($item['movie_slug']) ?>')" class="absolute top-1 right-1 w-6 h-6 bg-red-600 rounded-full text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-zinc-800/50 border-t border-zinc-800/50 p-6 text-center">
                    <a href="/" class="text-red-500 hover:text-red-400 font-medium transition-colors inline-flex items-center">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Trở về trang chủ
                    </a>
                </div>
            <?php else: ?>
                <div class="p-8">
                    <div class="text-center mb-8">
                        <a href="/" class="inline-flex items-center justify-center w-14 h-14 bg-red-600 rounded-2xl shadow-lg shadow-red-600/30 mb-4 transition-transform hover:scale-105">
                            <i data-lucide="play" class="w-8 h-8 text-white ml-1"></i>
                        </a>
                        <h1 class="text-2xl font-bold text-white mb-2" id="form-title"><?= $mode === 'register' ? 'Tạo Tài Khoản Mới' : 'Đăng Nhập Hệ Thống' ?></h1>
                        <p class="text-sm text-zinc-400" id="form-subtitle"><?= $mode === 'register' ? 'Chào mừng bạn đến với thế giới giải trí' : 'Mừng bạn trở lại với ' . htmlspecialchars($settings['siteName']) ?></p>
                    </div>

                    <?php if ($error): ?>
                        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 flex items-start space-x-3 text-red-400">
                            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                            <p class="text-sm font-medium"><?= htmlspecialchars($error) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 flex items-start space-x-3 text-green-400">
                            <i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                            <p class="text-sm font-medium"><?= htmlspecialchars($success) ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/api/auth.php" id="auth-form" class="space-y-5">
                        <input type="hidden" name="action" id="action-input" value="<?= $mode === 'register' ? 'register' : 'login' ?>">
                        
                        <div id="name-field" class="<?= $mode === 'register' ? 'block' : 'hidden' ?>">
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Tên hiển thị</label>
                            <div class="relative">
                                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-500"></i>
                                <input type="text" name="name" class="w-full bg-zinc-800/50 border border-zinc-700/50 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all placeholder-zinc-500" placeholder="Nguyễn Văn A">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Email</label>
                            <div class="relative">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-500"></i>
                                <input type="email" name="email" required class="w-full bg-zinc-800/50 border border-zinc-700/50 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all placeholder-zinc-500" placeholder="bạn@domain.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">Mật khẩu</label>
                            <div class="relative">
                                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-500"></i>
                                <input type="password" name="password" required class="w-full bg-zinc-800/50 border border-zinc-700/50 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all placeholder-zinc-500" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-lg shadow-red-600/30 flex items-center justify-center">
                            <i data-lucide="log-in" class="w-5 h-5 mr-2" id="submit-icon"></i> 
                            <span id="submit-text"><?= $mode === 'register' ? 'Đăng Ký Tài Khoản' : 'Đăng Nhập' ?></span>
                        </button>
                    </form>

                    <?php do_action('social_login_buttons'); ?>

                </div>

                <div class="bg-zinc-800/50 border-t border-zinc-800/50 p-6 text-center">
                    <p class="text-sm text-zinc-400" id="toggle-text">
                        <?= $mode === 'register' ? 'Đã có tài khoản?' : 'Chưa có tài khoản?' ?>
                        <button type="button" onclick="toggleMode()" class="text-red-500 hover:text-red-400 font-medium ml-1 transition-colors" id="toggle-btn">
                            <?= $mode === 'register' ? 'Đăng nhập ngay' : 'Đăng ký ngay' ?>
                        </button>
                    </p>
                </div>
            <?php endif; ?>
            
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Tabs Logic
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('active', 'text-white', 'border-red-500');
                    b.classList.add('text-zinc-400', 'border-transparent');
                });
                btn.classList.add('active', 'text-white', 'border-red-500');
                btn.classList.remove('text-zinc-400', 'border-transparent');

                document.querySelectorAll('.tab-content').forEach(c => {
                    c.classList.add('hidden');
                    c.classList.remove('block');
                });
                document.getElementById(btn.dataset.target).classList.remove('hidden');
                document.getElementById(btn.dataset.target).classList.add('block');
            });
        });

        function deletePlaylist(id) {
            if (!confirm('Bạn có chắc muốn xóa danh sách phát này?')) return;
            fetch('/api/playlists.php?action=delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ playlist_id: id })
            }).then(res => res.json()).then(res => {
                if (res.status === 'success') location.reload();
                else alert(res.message);
            });
        }

        function removePlaylistItem(playlistId, movieSlug) {
            if (!confirm('Bạn có chắc muốn xóa phim này khỏi danh sách?')) return;
            fetch('/api/playlists.php?action=remove_item', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ playlist_id: playlistId, movie_slug: movieSlug })
            }).then(res => res.json()).then(res => {
                if (res.status === 'success') location.reload();
                else alert(res.message);
            });
        }
        
        let currentMode = '<?= $mode ?>';
        
        function toggleMode() {
            currentMode = currentMode === 'login' ? 'register' : 'login';
            
            // Update URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('mode', currentMode);
            url.searchParams.delete('error');
            url.searchParams.delete('success');
            window.history.pushState({}, '', url);
            
            document.querySelectorAll('.bg-red-500\\/10, .bg-green-500\\/10').forEach(el => el.style.display = 'none');
            
            if (currentMode === 'register') {
                document.getElementById('form-title').innerText = 'Tạo Tài Khoản Mới';
                document.getElementById('form-subtitle').innerText = 'Chào mừng bạn đến với thế giới giải trí';
                document.getElementById('action-input').value = 'register';
                document.getElementById('name-field').classList.remove('hidden');
                document.getElementById('name-field').classList.add('block');
                document.getElementById('name-field').querySelector('input').setAttribute('required', 'required');
                
                document.getElementById('submit-text').innerText = 'Đăng Ký Tài Khoản';
                document.getElementById('submit-icon').setAttribute('data-lucide', 'user-plus');
                
                document.getElementById('toggle-text').childNodes[0].nodeValue = 'Đã có tài khoản? ';
                document.getElementById('toggle-btn').innerText = 'Đăng nhập ngay';
            } else {
                document.getElementById('form-title').innerText = 'Đăng Nhập Hệ Thống';
                document.getElementById('form-subtitle').innerText = 'Mừng bạn trở lại với <?= htmlspecialchars($settings['siteName']) ?>';
                document.getElementById('action-input').value = 'login';
                document.getElementById('name-field').classList.add('hidden');
                document.getElementById('name-field').classList.remove('block');
                document.getElementById('name-field').querySelector('input').removeAttribute('required');
                
                document.getElementById('submit-text').innerText = 'Đăng Nhập';
                document.getElementById('submit-icon').setAttribute('data-lucide', 'log-in');
                
                document.getElementById('toggle-text').childNodes[0].nodeValue = 'Chưa có tài khoản? ';
                document.getElementById('toggle-btn').innerText = 'Đăng ký ngay';
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
