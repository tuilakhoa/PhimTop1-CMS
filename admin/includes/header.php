<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PhimTop1</title>
    <link rel="stylesheet" href="/assets/css/style.min.css">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($settings['adminPath'] ?? '/admin') ?>/assets/css/admin.css?v=<?= time() ?>">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="PhimTop1 Admin">
    <link rel="manifest" href="/site.webmanifest">
    
    <?php if (!empty($settings['faviconUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['faviconUrl']) ?>">
    <?php elseif (!empty($settings['logoUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['logoUrl']) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <?php endif; ?>
</head>
<body class="bg-admin-bg text-gray-200 min-h-screen font-sans selection:bg-admin-primary selection:text-white relative bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-900 via-admin-bg to-admin-bg">
    
    <nav class="bg-admin-panel backdrop-blur-md border-b border-admin-border sticky top-0 z-50 transition-all duration-300">
        <div class="px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white transition-colors focus:outline-none p-2 rounded-lg hover:bg-white/5">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <a href="/" class="flex items-center space-x-3 text-white group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-admin-primary to-rose-700 flex items-center justify-center shadow-[0_0_15px_var(--tw-shadow-color)] shadow-admin-primaryGlow group-hover:scale-105 transition-transform duration-300">
                        <i data-lucide="play" class="w-5 h-5 fill-white text-white"></i>
                    </div>
                    <div class="hidden sm:flex flex-col">
                        <span class="font-bold text-lg leading-tight tracking-wide bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">PhimTop1</span>
                        <span class="text-[10px] text-admin-primary uppercase tracking-widest font-semibold">Workspace</span>
                    </div>
                </a>
            </div>
            <div class="flex items-center space-x-3">
                <a href="/" target="_blank" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2 border border-transparent hover:border-white/10">
                    <i data-lucide="external-link" class="w-4 h-4"></i> <span class="hidden sm:inline">Xem Web</span>
                </a>
                <a href="?action=logout" class="px-4 py-2 rounded-lg text-sm font-medium text-rose-400 hover:text-white hover:bg-rose-500 hover:shadow-[0_0_15px_var(--tw-shadow-color)] hover:shadow-rose-500/30 transition-all flex items-center gap-2 border border-rose-500/20 hover:border-transparent">
                    <i data-lucide="log-out" class="w-4 h-4"></i> <span class="hidden sm:inline">Đăng xuất</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
