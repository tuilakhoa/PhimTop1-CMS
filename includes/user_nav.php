<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$settings = getSettings();
?>
<div class="flex items-center">
    <?php if (isset($_SESSION['user'])): ?>
        <div class="relative group z-50">
            <button class="flex items-center space-x-2 focus:outline-none bg-gray-800/50 hover:bg-gray-700/50 px-2 py-1.5 md:px-3 md:py-2 rounded-full transition-all border border-gray-700/50">
                <img src="<?= htmlspecialchars($_SESSION['user']['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['name']) . '&background=random') ?>" class="w-6 h-6 md:w-8 md:h-8 rounded-full border border-gray-600 shadow-sm" alt="Avatar">
                <span class="hidden md:inline text-sm font-medium text-gray-200"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <i data-lucide="chevron-down" class="w-3 h-3 md:w-4 md:h-4 text-gray-400"></i>
            </button>
            <div class="absolute right-0 mt-2 w-56 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all origin-top-right transform scale-95 group-hover:scale-100 z-50">
                <div class="p-2">
                    <div class="px-4 py-3 border-b border-gray-800 mb-2">
                        <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                        <p class="text-xs text-gray-400 truncate mt-0.5"><?= htmlspecialchars($_SESSION['user']['email']) ?></p>
                    </div>
                    <a href="/api/auth.php?action=logout" class="flex items-center px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <a href="/member.php" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-medium transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
            <i data-lucide="user-circle" class="w-4 h-4 md:w-5 md:h-5 text-gray-200"></i>
            <span class="hidden md:inline">Đăng nhập</span>
        </a>
    <?php endif; ?>
</div>
