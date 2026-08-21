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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 flex items-center justify-center min-h-screen p-4 text-white font-sans">
    <div class="bg-gray-900 border border-gray-800 p-8 rounded-2xl max-w-md w-full shadow-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-transparent opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-center mb-6">
                <a href="/">
                    <?php if (!empty($settings['logoUrl'])): ?>
                        <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" class="h-10 object-contain">
                    <?php else: ?>
                        <div class="text-3xl font-black tracking-tighter text-red-600 uppercase">PhimTop1</div>
                    <?php endif; ?>
                </a>
            </div>
            <h2 class="text-2xl font-bold mb-2 text-center">Quên mật khẩu?</h2>
            <p class="text-gray-400 text-center mb-6 text-sm">Nhập email của bạn và chúng tôi sẽ gửi liên kết để đặt lại mật khẩu.</p>
            
            <div id="message" class="hidden mb-4 p-3 rounded-lg text-sm text-center"></div>
            
            <form id="forgotForm" class="space-y-5">
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-300">Địa chỉ Email</label>
                    <input type="email" id="email" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 focus:ring-1 focus:ring-red-500 outline-none transition-all" required placeholder="Nhập email đã đăng ký">
                </div>
                <button type="submit" id="submitBtn" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-lg transition-all shadow-lg shadow-red-600/25">Gửi email khôi phục</button>
            </form>
            <div class="mt-6 text-center">
                <a href="/" class="text-gray-500 hover:text-white text-sm transition-colors">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('forgotForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const btn = document.getElementById('submitBtn');
            const msg = document.getElementById('message');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="opacity-70">Đang gửi...</span>';
            
            try {
                const res = await fetch('/api/v1/auth.php?action=forgot_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ email })
                });
                const data = await res.json();
                msg.classList.remove('hidden', 'bg-red-500/10', 'border', 'border-red-500/50', 'text-red-400', 'bg-green-500/10', 'border-green-500/50', 'text-green-400');
                if (data.status === 'success') {
                    msg.classList.add('bg-green-500/10', 'border', 'border-green-500/50', 'text-green-400');
                    msg.textContent = data.message;
                    document.getElementById('forgotForm').reset();
                } else {
                    msg.classList.add('bg-red-500/10', 'border', 'border-red-500/50', 'text-red-400');
                    msg.textContent = data.message;
                }
            } catch (err) {
                msg.classList.remove('hidden', 'bg-green-500/10', 'border-green-500/50', 'text-green-400');
                msg.classList.add('bg-red-500/10', 'border', 'border-red-500/50', 'text-red-400');
                msg.textContent = 'Có lỗi xảy ra, vui lòng thử lại sau.';
            }
            
            btn.disabled = false;
            btn.textContent = 'Gửi email khôi phục';
        });
    </script>
</body>
</html>
