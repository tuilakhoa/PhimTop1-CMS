<?php
require_once __DIR__ . '/includes/db.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$settings = getSettings();

$isLoggedIn = isset($_SESSION['user']);
$user = $isLoggedIn ? $_SESSION['user'] : null;

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$mode = $_GET['mode'] ?? 'login'; // login or register

// Fetch follows if logged in
$follows = [];
if ($isLoggedIn) {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM user_follows WHERE user_email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $follows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
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
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #334155; border-radius: 20px; }
    </style>
</head>
<body class="bg-[#0f172a] min-h-screen relative overflow-x-hidden custom-scrollbar">
    <!-- Hiệu ứng Background -->
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="flex items-center justify-center min-h-screen w-full p-4 relative z-10">
        
        <div class="max-w-md w-full bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-3xl shadow-2xl overflow-hidden">
            <?php if ($isLoggedIn): ?>
                <div class="p-8">
                    <div class="flex items-center space-x-4 mb-8 border-b border-slate-700/50 pb-6">
                        <img src="<?= htmlspecialchars($user['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($user['name'])) ?>" alt="Avatar" class="w-16 h-16 rounded-full border-2 border-blue-500">
                        <div>
                            <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="ml-auto">
                            <a href="/api/auth.php?action=logout" class="bg-red-500/10 text-red-500 hover:bg-red-500/20 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                                <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Đăng xuất
                            </a>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i data-lucide="bookmark" class="w-5 h-5 mr-2 text-blue-500"></i> Phim/Truyện đang theo dõi
                    </h3>
                    
                    <?php if (empty($follows)): ?>
                        <div class="text-center py-8 text-gray-400 bg-slate-800/30 rounded-xl border border-slate-700/50">
                            <i data-lucide="film" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>Bạn chưa theo dõi nội dung nào.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <?php foreach ($follows as $item): ?>
                                <a href="<?= $item['item_type'] === 'comic' ? '/truyen-tranh/'.$item['item_slug'] : '/phim/'.$item['item_slug'] ?>" class="block group">
                                    <div class="relative aspect-[2/3] rounded-xl overflow-hidden mb-2 bg-slate-800">
                                        <img src="<?= htmlspecialchars($item['thumb_url']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md text-xs px-2 py-1 rounded text-white font-medium">
                                            <?= $item['item_type'] === 'comic' ? 'Truyện' : 'Phim' ?>
                                        </div>
                                    </div>
                                    <h4 class="text-white font-medium text-sm line-clamp-1 group-hover:text-blue-400 transition-colors"><?= htmlspecialchars($item['item_name']) ?></h4>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="bg-slate-800/50 border-t border-slate-700/50 p-6 text-center">
                    <a href="/" class="text-blue-400 hover:text-blue-300 font-medium transition-colors inline-flex items-center">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Trở về trang chủ
                    </a>
                </div>
            <?php else: ?>
                <div class="p-8">
                    <div class="text-center mb-8">
                        <a href="/" class="inline-flex items-center justify-center w-14 h-14 bg-blue-600 rounded-2xl shadow-lg shadow-blue-600/30 mb-4 transition-transform hover:scale-105">
                            <i data-lucide="play" class="w-8 h-8 text-white ml-1"></i>
                        </a>
                        <h1 class="text-2xl font-bold text-white mb-2" id="form-title"><?= $mode === 'register' ? 'Tạo Tài Khoản Mới' : 'Đăng Nhập Hệ Thống' ?></h1>
                        <p class="text-sm text-gray-400" id="form-subtitle"><?= $mode === 'register' ? 'Chào mừng bạn đến với thế giới giải trí' : 'Mừng bạn trở lại với ' . htmlspecialchars($settings['siteName']) ?></p>
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
                                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                                <input type="text" name="name" class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-gray-500" placeholder="Nguyễn Văn A">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                            <div class="relative">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                                <input type="email" name="email" required class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-gray-500" placeholder="bạn@domain.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Mật khẩu</label>
                            <div class="relative">
                                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                                <input type="password" name="password" required class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-gray-500" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center">
                            <i data-lucide="log-in" class="w-5 h-5 mr-2" id="submit-icon"></i> 
                            <span id="submit-text"><?= $mode === 'register' ? 'Đăng Ký Tài Khoản' : 'Đăng Nhập' ?></span>
                        </button>
                    </form>

                    <?php do_action('social_login_buttons'); ?>

                </div>

                <div class="bg-slate-800/50 border-t border-slate-700/50 p-6 text-center">
                    <p class="text-sm text-gray-400" id="toggle-text">
                        <?= $mode === 'register' ? 'Đã có tài khoản?' : 'Chưa có tài khoản?' ?>
                        <button type="button" onclick="toggleMode()" class="text-blue-400 hover:text-blue-300 font-medium ml-1 transition-colors" id="toggle-btn">
                            <?= $mode === 'register' ? 'Đăng nhập ngay' : 'Đăng ký ngay' ?>
                        </button>
                    </p>
                </div>
            <?php endif; ?>
            
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        let currentMode = '<?= $mode ?>';
        
        function toggleMode() {
            currentMode = currentMode === 'login' ? 'register' : 'login';
            
            // Update URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('mode', currentMode);
            // Clear error/success when toggling
            url.searchParams.delete('error');
            url.searchParams.delete('success');
            window.history.pushState({}, '', url);
            
            // Hide error/success blocks if they exist
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
