<?php
require_once __DIR__ . '/includes/db.php';
$settings = getSettings();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - <?= htmlspecialchars($settings['siteName']) ?></title>
    <link rel="stylesheet" href="/assets/css/style.min.css?v=<?= time() ?>">
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
        
        <div class="max-w-md w-full bg-[#111] backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl overflow-hidden p-8">
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center justify-center w-14 h-14 bg-phim-yellow rounded-2xl shadow-lg shadow-phim-yellow/20 mb-4">
                    <i data-lucide="key" class="w-8 h-8 text-black"></i>
                </a>
                <h2 class="text-2xl font-bold mb-2 text-white">Khôi phục mật khẩu</h2>
                <p class="text-gray-400 text-sm">Nhập email của bạn, chúng tôi sẽ gửi liên kết đổi mật khẩu.</p>
            </div>
            
            <div id="message" class="hidden mb-6 p-4 rounded-xl flex items-start space-x-3 text-sm font-medium"></div>
            
            <form id="forgotForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Địa chỉ Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="email" id="email" class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow placeholder-gray-600 transition-colors" required placeholder="bạn@domain.com">
                    </div>
                </div>
                <button type="submit" id="submitBtn" class="w-full bg-phim-yellow hover:bg-yellow-400 text-black font-bold py-3 px-4 rounded-xl shadow-[0_0_15px_rgba(234,179,8,0.3)] flex items-center justify-center transition-all">
                    <i data-lucide="send" class="w-5 h-5 mr-2"></i> Gửi liên kết
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <a href="/?mode=login" class="text-gray-400 hover:text-white font-medium inline-flex items-center transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Trở về đăng nhập
                </a>
            </div>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
        document.getElementById('forgotForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const btn = document.getElementById('submitBtn');
            const msg = document.getElementById('message');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="opacity-70 flex items-center"><i data-lucide="loader" class="w-5 h-5 mr-2 animate-spin"></i> Đang gửi...</span>';
            lucide.createIcons();
            
            try {
                const res = await fetch('/api/v1/auth.php?action=forgot_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ email })
                });
                const data = await res.json();
                msg.className = 'mb-6 p-4 rounded-xl flex items-start space-x-3 text-sm font-medium border';
                if (data.status === 'success') {
                    msg.classList.add('bg-green-500/10', 'border-green-500/30', 'text-green-400');
                    msg.innerHTML = '<i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0 mt-0.5"></i><p>' + data.message + '</p>';
                    document.getElementById('forgotForm').reset();
                } else {
                    msg.classList.add('bg-red-500/10', 'border-red-500/30', 'text-red-400');
                    msg.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i><p>' + data.message + '</p>';
                }
                msg.classList.remove('hidden');
            } catch (err) {
                msg.className = 'mb-6 p-4 rounded-xl flex items-start space-x-3 text-sm font-medium border bg-red-500/10 border-red-500/30 text-red-400';
                msg.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i><p>Có lỗi xảy ra, vui lòng thử lại sau.</p>';
                msg.classList.remove('hidden');
            }
            
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="send" class="w-5 h-5 mr-2"></i> Gửi liên kết';
            lucide.createIcons();
        });
    </script>
</body>
</html>
