<?php
$settings = getSettings();
$theme = $settings['theme'] ?? 'dark';
$themeClasses = [
    'dark' => 'bg-gray-950 text-gray-100',
    'netflix' => 'bg-black text-gray-200',
    'light' => 'bg-gray-900 text-white',
    'cyberpunk' => 'bg-[#0a0a0a] text-yellow-400',
    'ocean' => 'bg-blue-950 text-blue-50',
    'nature' => 'bg-green-950 text-green-50'
];
$bodyClass = $themeClasses[$theme] ?? $themeClasses['dark'];

// Fallback logic for dynamic SEO (will be overridden by movie.php if set)
global $pageTitle, $pageDesc, $pageKeywords;
$seoTitle = $pageTitle ?? ($settings['seoTitle'] ?? 'PhimTop1 - Xem Phim Online');
$seoDesc = $pageDesc ?? ($settings['seoDesc'] ?? 'Hệ thống xem phim trực tuyến chất lượng cao');
$seoKeywords = $pageKeywords ?? ($settings['seoKeywords'] ?? 'xem phim, phim online');
$siteName = $settings['siteName'] ?? 'PhimTop1';

// Fetch Categories
$pdo = getPDO();
$genres = [];
$countries = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['type'] === 'genre') $genres[] = $row;
        else if ($row['type'] === 'country') $countries[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seoTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDesc) ?>">
    <meta name="robots" content="index, follow">
    
    <?php if (!empty($settings['verifyGoogle'])): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($settings['verifyGoogle']) ?>" />
    <?php endif; ?>
    <?php if (!empty($settings['verifyBing'])): ?>
    <meta name="msvalidate.01" content="<?= htmlspecialchars($settings['verifyBing']) ?>" />
    <?php endif; ?>
    <?php if (!empty($settings['verifyYandex'])): ?>
    <meta name="yandex-verification" content="<?= htmlspecialchars($settings['verifyYandex']) ?>" />
    <?php endif; ?>

    <meta name="keywords" content="<?= htmlspecialchars($seoKeywords) ?>">
    <?php if (!empty($settings['appleTouchIconUrl'])): ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars($settings['appleTouchIconUrl']) ?>">
    <?php elseif (!empty($settings['useLogoAsFavicon']) && !empty($settings['logoUrl'])): ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars($settings['logoUrl']) ?>">
    <?php else: ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <?php endif; ?>
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($siteName ?? 'PhimTop1') ?>">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Favicon -->
    <?php if (!empty($settings['faviconUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['faviconUrl']) ?>">
    <?php elseif (!empty($settings['useLogoAsFavicon']) && !empty($settings['logoUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['logoUrl']) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <?php endif; ?>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: {
                DEFAULT: '#FACC15', // Vibrant yellow matching app logo
                dark: '#EAB308',
                glow: 'rgba(250, 204, 21, 0.4)'
              },
              dark: {
                900: '#0a0a0a',
                800: '#111111',
                700: '#1a1a1a',
              }
            },
            animation: {
              'fade-in': 'fadeIn 0.5s ease-out',
            },
            keyframes: {
              fadeIn: {
                '0%': { opacity: '0', transform: 'translateY(10px)' },
                '100%': { opacity: '1', transform: 'translateY(0)' },
              }
            }
          }
        }
      }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/themes/dark/assets/css/style.css?v=<?= time() ?>">
    
    <!-- Cấu hình Đo lường & Bảo mật (Cloudflare/GA4) -->
    <?php if (!empty($settings['cfAnalyticsToken'])): ?>
    <script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "<?= htmlspecialchars($settings['cfAnalyticsToken']) ?>"}'></script>
    <?php endif; ?>
    
    <?php if (!empty($settings['gaMeasurementId'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($settings['gaMeasurementId']) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= htmlspecialchars($settings['gaMeasurementId']) ?>');
    </script>
    <?php endif; ?>
    
    <?php if (!empty($settings['cfTurnstileKey'])): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>

    <?php if (!empty($settings['customHead'])): ?>
        <?= $settings['customHead'] ?>
    <?php endif; ?>
    <?php do_action('cms_head'); ?>
</head>
<body class="<?= $bodyClass ?> min-h-screen selection:bg-primary selection:text-black">
    <nav class="glass-nav fixed w-full top-0 z-50 bg-black/60 backdrop-blur-xl border-b border-white/5 transition-all duration-300" id="mainNav">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left Section: Logo & Nav -->
                <div class="flex items-center space-x-8">
                    <!-- Logo -->
                    <a href="/" class="flex items-center space-x-2">
                        <?php if (!empty($settings['logoUrl'])): ?>
                            <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" class="w-8 h-8 object-contain">
                        <?php else: ?>
                            <div class="w-10 h-10 bg-gray-800/20 rounded-xl flex items-center justify-center backdrop-blur-md">
                                <i data-lucide="monitor-play" class="w-6 h-6 text-white"></i>
                            </div>
                        <?php endif; ?>
                        <span class="text-2xl font-bold tracking-tight text-white"><?= htmlspecialchars($siteName) ?></span>
                    </a>
                    
                    <!-- Desktop Nav Links -->
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="text-gray-300 hover:text-white transition-colors">Phim Lẻ</a>
                        <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo" class="text-gray-300 hover:text-white transition-colors">Phim Bộ</a>
                        <a href="/bang-xep-hang" class="text-[#00E359] font-semibold hover:text-white transition-colors flex items-center" title="Bảng Xếp Hạng"><i data-lucide="trending-up" class="w-4 h-4 mr-1"></i> BXH</a>
                        <?php do_action('theme_header_menu'); ?>
                        
                        <!-- Dropdown Thể Loại -->
                        <div class="relative group">
                            <button class="text-gray-300 hover:text-white transition-colors flex items-center">
                                Thể Loại <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-96 bg-gray-900 border border-gray-800 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                <div class="p-4 grid grid-cols-3 gap-2">
                                    <?php foreach ($genres as $g): ?>
                                        <a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/<?= htmlspecialchars($g['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded transition-colors truncate"><?= htmlspecialchars($g['name']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dropdown Quốc Gia -->
                        <div class="relative group">
                            <button class="text-gray-300 hover:text-white transition-colors flex items-center">
                                Quốc Gia <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-96 bg-gray-900 border border-gray-800 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                <div class="p-4 grid grid-cols-3 gap-2">
                                    <?php foreach ($countries as $c): ?>
                                        <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/<?= htmlspecialchars($c['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded transition-colors truncate"><?= htmlspecialchars($c['name']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Section: Search & Admin -->
                <div class="flex items-center space-x-6">
                    <form action="/search" method="GET" class="relative hidden md:block group">
                        <input type="text" name="keyword" placeholder="Tìm kiếm phim..." 
                            class="bg-dark-800/80 text-gray-200 text-sm rounded-full pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary border border-white/10 w-64 transition-all duration-300 focus:w-80 focus:bg-dark-700">
                        <i data-lucide="search" class="absolute left-3 top-3 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                    </form>
                    
                    <?php if (!empty($settings['appDownloadUrl'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="hidden md:flex items-center bg-red-600 hover:bg-red-500 text-white text-sm font-bold py-2 px-4 rounded-full transition-colors shadow-[0_0_15px_rgba(220,38,38,0.3)] border border-red-500/50 mr-2">
                        <i data-lucide="smartphone" class="w-4 h-4 mr-1.5"></i> Tải App Mobile
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['appDownloadUrlTv'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrlTv']) ?>" target="_blank" class="hidden md:flex items-center bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold py-2 px-4 rounded-full transition-colors border border-gray-700 mr-2">
                        <i data-lucide="tv" class="w-4 h-4 mr-1.5"></i> Tải App TV
                    </a>
                    <?php endif; ?>

                    <?php include __DIR__ . '/../../includes/user_nav.php'; ?>

                    <!-- Mobile Search Button (replaces hamburger) -->
                    <div class="md:hidden flex items-center">
                        <button id="mobileSearchBtn" class="text-gray-300 hover:text-white focus:outline-none p-2 rounded-full hover:bg-white/10 transition-colors">
                            <i data-lucide="search" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Search Bar (Hidden by default) -->
        <div id="mobileSearch" class="md:hidden hidden bg-dark-900/95 backdrop-blur-xl border-t border-white/5 absolute w-full left-0 top-16 shadow-2xl p-4">
            <form action="/search" method="GET" class="relative">
                <input type="text" name="keyword" placeholder="Tìm kiếm phim..." 
                    class="w-full bg-dark-800 text-white text-sm rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary border border-white/10 shadow-inner">
                <i data-lucide="search" class="absolute left-3 top-3.5 w-5 h-5 text-gray-400"></i>
            </form>
            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="bg-dark-800 py-2 px-3 rounded-lg text-center text-gray-300 hover:text-primary border border-white/5">Phim Lẻ</a>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo" class="bg-dark-800 py-2 px-3 rounded-lg text-center text-gray-300 hover:text-primary border border-white/5">Phim Bộ</a>
                <a href="/bang-xep-hang" class="bg-dark-800 py-2 px-3 rounded-lg text-center text-gray-300 hover:text-primary border border-white/5 col-span-2 flex justify-center items-center"><i data-lucide="trending-up" class="w-4 h-4 mr-2 text-primary"></i> Bảng Xếp Hạng</a>
            </div>
        </div>
    </nav>
    <script src="/assets/js/main.js?v=<?= time() ?>"></script>
    <script>
        // Header scroll effect & Mobile Search Toggle
        document.addEventListener('DOMContentLoaded', () => {
            const nav = document.getElementById('mainNav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 20) {
                    nav.classList.add('shadow-lg', 'bg-black/80');
                    nav.classList.remove('bg-black/60');
                } else {
                    nav.classList.remove('shadow-lg', 'bg-black/80');
                    nav.classList.add('bg-black/60');
                }
            });

            const searchBtn = document.getElementById('mobileSearchBtn');
            const searchPanel = document.getElementById('mobileSearch');
            if(searchBtn && searchPanel) {
                searchBtn.addEventListener('click', () => {
                    searchPanel.classList.toggle('hidden');
                    if(!searchPanel.classList.contains('hidden')) {
                        searchPanel.querySelector('input').focus();
                    }
                });
            }
        });
    </script>
    <div class="pt-20 pb-24 md:pb-12">
