<?php
require_once __DIR__ . '/includes/db.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$settings = getSettings();

if (isset($_SESSION['user'])) {
    header("Location: /");
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$mode = $_GET['mode'] ?? 'login'; // login or register
$googleClientId = $settings['googleClientId'] ?? '';
$msClientId = $settings['msClientId'] ?? '';
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

                <?php if ($googleClientId || $msClientId): ?>
                <div class="mt-6">
                    <div class="relative flex items-center mb-6">
                        <div class="flex-grow border-t border-slate-700/50"></div>
                        <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">hoặc tiếp tục với</span>
                        <div class="flex-grow border-t border-slate-700/50"></div>
                    </div>
                    
                    <div class="flex flex-col space-y-3">
                        <?php if ($googleClientId): ?>
                        <a href="/api/auth.php?action=google_login" class="w-full flex items-center justify-center space-x-2 bg-white hover:bg-gray-100 text-gray-900 font-semibold py-3 px-4 rounded-xl transition-all shadow-md">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            <span>Google</span>
                        </a>
                        <?php endif; ?>

                        <?php if ($msClientId): ?>
                        <a href="/api/auth.php?action=microsoft_login" class="w-full flex items-center justify-center space-x-2 bg-[#2F2F2F] hover:bg-[#3F3F3F] text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-md border border-slate-700/50">
                            <svg class="w-5 h-5" viewBox="0 0 21 21">
                                <path fill="#f25022" d="M1 1h9v9H1z"/><path fill="#00a4ef" d="M1 11h9v9H1z"/><path fill="#7fba00" d="M11 1h9v9h-9z"/><path fill="#ffb900" d="M11 11h9v9h-9z"/>
                            </svg>
                            <span>Microsoft</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <div class="bg-slate-800/50 border-t border-slate-700/50 p-6 text-center">
                <p class="text-sm text-gray-400" id="toggle-text">
                    <?= $mode === 'register' ? 'Đã có tài khoản?' : 'Chưa có tài khoản?' ?>
                    <button type="button" onclick="toggleMode()" class="text-blue-400 hover:text-blue-300 font-medium ml-1 transition-colors" id="toggle-btn">
                        <?= $mode === 'register' ? 'Đăng nhập ngay' : 'Đăng ký ngay' ?>
                    </button>
                </p>
            </div>
            
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
