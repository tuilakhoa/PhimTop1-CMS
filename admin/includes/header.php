<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PhimTop1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($settings['adminPath'] ?? '/admin') ?>/assets/css/admin.css?v=<?= time() ?>">
    <!-- Favicon -->
    <?php if (!empty($settings['faviconUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['faviconUrl']) ?>">
    <?php elseif (!empty($settings['logoUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['logoUrl']) ?>">
    <?php else: ?>
    <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><rect width='40' height='40' rx='10' fill='%234b5563'/><svg x='8' y='8' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect width='20' height='14' x='2' y='3' rx='2'/><path d='M12 17v4'/><path d='M8 21h8'/><polygon points='10 7 15 10 10 13 10 7'/></svg></svg>">
    <?php endif; ?>
</head>
<body class="bg-gray-950 text-gray-200 min-h-screen">
    
    <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50">
        <div class="px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <a href="/" class="flex items-center space-x-2 text-white hover:text-red-500 transition-colors">
                    <i data-lucide="play" class="w-6 h-6 fill-current text-red-600"></i>
                    <span class="font-bold text-xl hidden sm:block">PhimTop1 Admin</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/" target="_blank" class="text-gray-400 hover:text-white flex items-center text-sm">
                    <i data-lucide="external-link" class="w-4 h-4 mr-1"></i> Xem Web
                </a>
                <a href="?action=logout" class="text-red-400 hover:text-red-300 flex items-center text-sm font-medium">
                    <i data-lucide="log-out" class="w-4 h-4 mr-1"></i> Đăng xuất
                </a>
            </div>
        </div>
    </nav>

    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
