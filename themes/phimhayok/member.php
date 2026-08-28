<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thành Viên - <?= htmlspecialchars($settings['siteName']) ?></title>
    <link rel="stylesheet" href="/assets/css/style.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #333; border-radius: 20px; }
    </style>
</head>
<body class="bg-black min-h-screen relative overflow-x-hidden custom-scrollbar">
    <!-- Hiệu ứng Background (Phimhayok Theme: Yellow & Black) -->
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-phim-yellow/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="flex items-center justify-center min-h-screen w-full p-4 relative z-10">
        
        <div class="max-w-md w-full bg-[#111] backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl overflow-hidden">
            <?php if ($isLoggedIn): ?>
                <div class="p-8">
                    <div class="flex items-center space-x-4 mb-8 border-b border-white/10 pb-6">
                        <img loading="lazy" src="<?= htmlspecialchars($user['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($user['name'])) ?>" alt="Avatar" class="w-16 h-16 rounded-full border-2 border-phim-yellow">
                        <div>
                            <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="ml-auto">
                            <a href="/api/auth.php?action=logout" class="bg-red-500/10 text-red-500 hover:bg-red-500/20 px-4 py-2 rounded-lg font-medium transition-colors flex items-center border border-red-500/30">
                                <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Thoát
                            </a>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i data-lucide="bookmark" class="w-5 h-5 mr-2 text-phim-yellow"></i> Đang theo dõi
                    </h3>
                    
                    <?php if (empty($follows)): ?>
                        <div class="text-center py-8 text-gray-500 bg-[#1a1a1a] rounded-xl border border-white/5">
                            <i data-lucide="film" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
                            <p>Bạn chưa theo dõi nội dung nào.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <?php foreach ($follows as $item): ?>
                                <a href="<?= $item['item_type'] === 'comic' ? '/truyen-tranh/'.$item['item_slug'] : '/phim/'.$item['item_slug'] ?>" class="block group">
                                    <div class="relative aspect-[2/3] rounded-lg overflow-hidden mb-2 bg-[#1a1a1a]">
                                        <img loading="lazy" src="<?= htmlspecialchars($item['thumb_url']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="absolute top-2 right-2 bg-phim-yellow text-black text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">
                                            <?= $item['item_type'] === 'comic' ? 'Truyện' : 'Phim' ?>
                                        </div>
                                    </div>
                                    <h4 class="text-white font-bold text-sm line-clamp-1 group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['item_name']) ?></h4>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="bg-[#1a1a1a] border-t border-white/5 p-6 text-center">
                    <a href="/" class="text-gray-400 hover:text-white font-medium transition-colors inline-flex items-center">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Trở về trang chủ
                    </a>
                </div>
            <?php else: ?>
                <div class="p-8">
                    <div class="text-center mb-8">
                        <a href="/" class="inline-flex items-center justify-center w-14 h-14 bg-phim-yellow rounded-2xl shadow-lg shadow-phim-yellow/20 mb-4 transition-transform hover:scale-105">
                            <i data-lucide="play" class="w-8 h-8 text-black ml-1"></i>
                        </a>
                                            <div class="flex p-1 bg-[#1a1a1a] rounded-xl mb-8 relative border border-white/5">
                        <div id="tab-indicator" class="absolute top-1 bottom-1 w-[calc(50%-4px)] bg-phim-yellow rounded-lg shadow-md transition-transform duration-300 ease-out <?= $mode === 'register' ? 'translate-x-[100%]' : 'translate-x-0' ?>"></div>
                        
                        <button type="button" onclick="setMode('login')" id="tab-login" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-gray-400 hover:text-white' : 'text-black' ?>">Đăng Nhập</button>
                        
                        <button type="button" onclick="setMode('register')" id="tab-register" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-black' : 'text-gray-400 hover:text-white' ?>">Đăng Ký</button>
                    </div>
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
                            <label class="block text-sm font-medium text-gray-300 mb-2">Tên hiển thị</label>
                            <div class="relative">
                                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="text" name="name" class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow transition-all placeholder-gray-600" placeholder="Nguyễn Văn A">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                            <div class="relative">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="email" name="email" required class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow transition-all placeholder-gray-600" placeholder="bạn@domain.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2 flex justify-between">
                                <span>Mật khẩu</span>
                                <a href="/forgot_password.php" id="forgot-link" class="text-xs text-phim-yellow hover:text-white transition-colors <?= $mode === 'register' ? 'hidden' : 'block' ?>">Quên mật khẩu?</a>
                            </label>
                            <div class="relative">
                                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="password" name="password" required class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow transition-all placeholder-gray-600" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full bg-phim-yellow hover:bg-yellow-400 text-black font-bold py-3 px-4 rounded-xl transition-all shadow-[0_0_15px_rgba(234,179,8,0.3)] flex items-center justify-center">
                            <i data-lucide="log-in" class="w-5 h-5 mr-2" id="submit-icon"></i> 
                            <span id="submit-text"><?= $mode === 'register' ? 'Đăng Ký Tài Khoản' : 'Đăng Nhập' ?></span>
                        </button>
                    </form>

                    <?php do_action('social_login_buttons'); ?>

                </div>

                
            <?php endif; ?>
            
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        let currentMode = '<?= $mode ?>';
        
        function setMode(newMode) {
            currentMode = newMode;
            
            const url = new URL(window.location);
            url.searchParams.set('mode', currentMode);
            url.searchParams.delete('error');
            url.searchParams.delete('success');
            window.history.pushState({}, '', url);
            
            document.querySelectorAll('.bg-red-500\/10, .bg-green-500\/10').forEach(el => el.style.display = 'none');
            
            if (currentMode === 'register') {
                document.getElementById('action-input').value = 'register';
                document.getElementById('name-field').classList.remove('hidden');
                document.getElementById('name-field').classList.add('block');
                document.getElementById('name-field').querySelector('input').setAttribute('required', 'required');
                const forgotLink = document.getElementById('forgot-link');
                if(forgotLink) forgotLink.style.display = 'none';
                
                document.getElementById('submit-text').innerText = 'Đăng Ký Tài Khoản';
                document.getElementById('submit-icon').setAttribute('data-lucide', 'user-plus');
                
                const indicator = document.getElementById('tab-indicator');
                if(indicator) {
                    indicator.classList.remove('translate-x-0');
                    indicator.classList.add('translate-x-[100%]');
                }
                const tabLogin = document.getElementById('tab-login');
                if(tabLogin) tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-gray-400 hover:text-white';
                const tabRegister = document.getElementById('tab-register');
                if(tabRegister) tabRegister.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-black';
            } else {
                document.getElementById('action-input').value = 'login';
                document.getElementById('name-field').classList.add('hidden');
                document.getElementById('name-field').classList.remove('block');
                document.getElementById('name-field').querySelector('input').removeAttribute('required');
                const forgotLink = document.getElementById('forgot-link');
                if(forgotLink) forgotLink.style.display = 'block';
                
                document.getElementById('submit-text').innerText = 'Đăng Nhập';
                document.getElementById('submit-icon').setAttribute('data-lucide', 'log-in');
                
                const indicator = document.getElementById('tab-indicator');
                if(indicator) {
                    indicator.classList.remove('translate-x-[100%]');
                    indicator.classList.add('translate-x-0');
                }
                const tabLogin = document.getElementById('tab-login');
                if(tabLogin) tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-black';
                const tabRegister = document.getElementById('tab-register');
                if(tabRegister) tabRegister.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-gray-400 hover:text-white';
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
