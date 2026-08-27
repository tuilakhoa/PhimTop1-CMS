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

function getNormalizedUrl($url) {
    if (empty($url)) return '';
    if (strpos($url, 'http') === 0 || strpos($url, '/') === 0) return $url;
    return '/' . $url;
}

// Fetch Categories with Cache
require_once __DIR__ . '/../../includes/cache_manager.php';
$cache = new CacheManager();
$cachedCats = $cache->get('site_categories_all', 86400); // 1 day
$genres = [];
$countries = [];

if ($cachedCats) {
    $cats = json_decode($cachedCats, true);
    $genres = $cats['genres'] ?? [];
    $countries = $cats['countries'] ?? [];
} else {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['type'] === 'genre') $genres[] = $row;
            else if ($row['type'] === 'country') $countries[] = $row;
        }
        $cache->set('site_categories_all', json_encode(['genres' => $genres, 'countries' => $countries]));
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
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars(getNormalizedUrl($settings['appleTouchIconUrl'])) ?>">
    <?php elseif (!empty($settings['useLogoAsFavicon']) && !empty($settings['logoUrl'])): ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars(getNormalizedUrl($settings['logoUrl'])) ?>">
    <?php else: ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <?php endif; ?>
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($siteName ?? 'PhimTop1') ?>">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Favicon -->
    <?php if (!empty($settings['faviconUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars(getNormalizedUrl($settings['faviconUrl'])) ?>">
    <?php elseif (!empty($settings['useLogoAsFavicon']) && !empty($settings['logoUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars(getNormalizedUrl($settings['logoUrl'])) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <?php endif; ?>
    <!-- Preconnect to CDNs to improve speed -->
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="preconnect" href="https://phimimg.com" crossorigin>
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script defer src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/themes/dark/assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
    <style>
        /* CSS Optimizations for Mobile */
        img { content-visibility: auto; }
        .custom-scrollbar { -webkit-overflow-scrolling: touch; overscroll-behavior-x: contain; }
        * { -webkit-tap-highlight-color: transparent; }
        .group-hover\:scale-105 { will-change: transform; transform-style: preserve-3d; }
    </style>
    
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
<body class="<?= $bodyClass ?> min-h-screen">
    <nav class="glass-nav fixed w-full top-0 z-50">
        <div class="w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto">
            <div class="flex items-center justify-between h-16">
                <!-- Left Section: Logo & Nav -->
                <div class="flex items-center space-x-8">
                    <!-- Logo -->
                    <a href="/" class="flex items-center space-x-2">
                        <?php if (!empty($settings['logoUrl'])): ?>
                            <img src="<?= htmlspecialchars(getNormalizedUrl($settings['logoUrl'])) ?>" alt="Logo" decoding="async" class="w-8 h-8 object-contain">
                        <?php else: ?>
                            <div class="w-10 h-10 bg-gray-800/50 rounded-xl flex items-center justify-center">
                                <i data-lucide="monitor-play" class="w-6 h-6 text-white"></i>
                            </div>
                        <?php endif; ?>
                        <span class="text-2xl font-bold tracking-tight text-white"><?= htmlspecialchars($siteName) ?></span>
                    </a>
                    
                    <!-- Desktop Nav Links -->
                    <div class="hidden lg:flex items-center space-x-6">
                        <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="text-gray-300 hover:text-white transition-colors">Phim Lẻ</a>
                        <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo" class="text-gray-300 hover:text-white transition-colors">Phim Bộ</a>
                        <a href="/bang-xep-hang" class="text-[#00E359] font-semibold hover:text-white transition-colors flex items-center" title="Bảng Xếp Hạng"><i data-lucide="trending-up" class="w-4 h-4 mr-1"></i> BXH</a>
                        <button onclick="openGlobalWatchParty()" class="text-[#8B5CF6] font-semibold hover:text-white transition-colors flex items-center" title="Phòng Xem Chung"><i data-lucide="users" class="w-4 h-4 mr-1"></i> Xem Chung</button>
                        <?php do_action('theme_header_menu'); ?>
                        
                        <!-- Dropdown Khám Phá -->
                        <div class="relative group">
                            <button class="text-gray-300 hover:text-white transition-colors flex items-center font-medium">
                                Khám Phá <i data-lucide="grid" class="w-4 h-4 ml-1"></i>
                            </button>
                            <div class="absolute left-1/2 -translate-x-1/2 mt-2 w-[600px] bg-gray-900 border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="p-6 flex gap-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                    <div class="flex-1">
                                        <h3 class="text-white font-bold mb-3 flex items-center border-b border-gray-800 pb-2">
                                            <i data-lucide="film" class="w-4 h-4 mr-2 text-red-500"></i> Thể Loại
                                        </h3>
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                            <?php foreach ($genres as $g): ?>
                                                <a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/<?= htmlspecialchars($g['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1.5 rounded transition-colors truncate"><?= htmlspecialchars($g['name']) ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="flex-1 border-l border-gray-800 pl-6">
                                        <h3 class="text-white font-bold mb-3 flex items-center border-b border-gray-800 pb-2">
                                            <i data-lucide="globe" class="w-4 h-4 mr-2 text-blue-500"></i> Quốc Gia
                                        </h3>
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                            <?php foreach ($countries as $c): ?>
                                                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/<?= htmlspecialchars($c['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1.5 rounded transition-colors truncate"><?= htmlspecialchars($c['name']) ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Section: Search & Admin -->
                <div class="flex items-center space-x-6">
                    <form action="/search" method="GET" class="relative hidden lg:block">
                        <input type="text" name="keyword" placeholder="Tìm kiếm phim..." 
                            class="bg-gray-800/50 text-gray-200 text-sm rounded-full pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 border border-gray-700 w-64 transition-all focus:w-80">
                        <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                    </form>
                    
                    <?php if (!empty($settings['appDownloadUrl'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="hidden lg:flex items-center justify-center w-9 h-9 rounded-full bg-gray-800/50 text-gray-300 hover:text-white hover:bg-gray-700 transition-colors border border-gray-700/50 mr-2" title="Tải Ứng Dụng">
                        <i data-lucide="smartphone" class="w-4 h-4"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['appDownloadUrlTv'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrlTv']) ?>" target="_blank" class="hidden lg:flex items-center justify-center w-9 h-9 rounded-full bg-gray-800/50 text-gray-300 hover:text-white hover:bg-gray-700 transition-colors border border-gray-700/50 mr-2" title="Tải App TV">
                        <i data-lucide="tv" class="w-4 h-4"></i>
                    </a>
                    <?php endif; ?>

                    <?php include __DIR__ . '/../../includes/user_nav.php'; ?>

                    <!-- Mobile Menu Button -->
                    <div class="lg:hidden flex items-center">
                        <button id="mobileMenuBtn" class="text-gray-300 hover:text-white focus:outline-none">
                            <i data-lucide="menu" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobileMenu" class="lg:hidden hidden bg-gray-900 border-t border-gray-800 absolute w-full left-0 top-16 shadow-2xl">
            <div class="px-4 py-4 space-y-4">
                <form action="/search" method="GET" class="relative">
                    <input type="text" name="keyword" placeholder="Tìm kiếm phim..." 
                        class="w-full bg-gray-800/50 text-gray-200 text-sm rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 border border-gray-700">
                    <i data-lucide="search" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                </form>
                <div class="flex flex-col space-y-3 font-medium text-gray-300">
                    <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="hover:text-white">Phim Lẻ</a>
                    <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo" class="hover:text-white">Phim Bộ</a>
                    <a href="/bang-xep-hang" class="text-[#00E359] font-bold flex items-center"><i data-lucide="trending-up" class="w-4 h-4 mr-2"></i> Bảng Xếp Hạng</a>
                    <button onclick="openGlobalWatchParty()" class="text-[#8B5CF6] font-bold flex items-center text-left"><i data-lucide="users" class="w-4 h-4 mr-2"></i> Phòng Xem Chung</button>
                    <?php do_action('theme_mobile_menu'); ?>
                    
                    <?php if (!empty($settings['appDownloadUrl'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrl']) ?>" target="_blank" class="flex items-center hover:text-white mt-1">
                        <i data-lucide="smartphone" class="w-4 h-4 mr-2 text-gray-400"></i> Tải Ứng Dụng Mobile
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['appDownloadUrlTv'])): ?>
                    <a href="<?= htmlspecialchars($settings['appDownloadUrlTv']) ?>" target="_blank" class="flex items-center hover:text-white mt-1">
                        <i data-lucide="tv" class="w-4 h-4 mr-2 text-gray-400"></i> Tải Ứng Dụng TV
                    </a>
                    <?php endif; ?>
                    
                    <div class="border-t border-gray-800 pt-2 pb-1">
                        <details class="group">
                            <summary class="flex items-center justify-between cursor-pointer hover:text-white py-1 list-none [&::-webkit-details-marker]:hidden">
                                <span class="text-gray-300 font-bold uppercase tracking-wider text-sm">Thể Loại</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="grid grid-cols-2 gap-2 mt-2 mb-2 pl-4 border-l border-gray-800">
                                <?php foreach ($genres as $g): ?>
                                    <a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/<?= htmlspecialchars($g['slug']) ?>" class="text-sm text-gray-400 hover:text-white truncate"><?= htmlspecialchars($g['name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    </div>
                    <div class="border-t border-gray-800 pt-2 pb-1">
                        <details class="group">
                            <summary class="flex items-center justify-between cursor-pointer hover:text-white py-1 list-none [&::-webkit-details-marker]:hidden">
                                <span class="text-gray-300 font-bold uppercase tracking-wider text-sm">Quốc Gia</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="grid grid-cols-2 gap-2 mt-2 mb-2 pl-4 border-l border-gray-800">
                                <?php foreach ($countries as $c): ?>
                                    <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/<?= htmlspecialchars($c['slug']) ?>" class="text-sm text-gray-400 hover:text-white truncate"><?= htmlspecialchars($c['name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    </div>
                    <div class="border-t border-gray-800 pt-2 pb-1">
                        <details class="group">
                            <summary class="flex items-center justify-between cursor-pointer hover:text-white py-1 list-none [&::-webkit-details-marker]:hidden">
                                <span class="text-gray-300 font-bold uppercase tracking-wider text-sm">Năm Phát Hành</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="grid grid-cols-3 gap-2 mt-2 mb-2 pl-4 border-l border-gray-800">
                                <?php for($y = date('Y'); $y >= 2010; $y--): ?>
                                    <a href="/nam/<?= $y ?>" class="text-sm text-gray-400 hover:text-white"><?= $y ?></a>
                                <?php endfor; ?>
                            </div>
                        </details>
                    </div>

                </div>
            </div>
        </div>
    </nav>
    <script src="/assets/js/main.js?v=<?= filemtime(__DIR__ . '/../../assets/js/main.js') ?>"></script>
    <script>
    function openGlobalWatchParty() {
        const code = prompt("Nhập MÃ PHÒNG xem chung:");
        if (code && code.trim() !== "") {
            fetch('/api/v1/watch_party.php?action=join&room_code=' + code.trim().toUpperCase())
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '/phim/' + data.data.movie_slug + '?party=' + data.data.room_code;
                } else {
                    alert('Lỗi: ' + data.message);
                }
            }).catch(e => alert('Có lỗi xảy ra, vui lòng thử lại!'));
        }
    }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForms = document.querySelectorAll('form[action="/search"]');
        searchForms.forEach(form => {
            const input = form.querySelector('input[name="keyword"]');
            if (!input) return;
            
            input.setAttribute('autocomplete', 'off');
            
            let container = document.createElement('div');
            container.className = 'absolute top-full left-0 mt-2 w-full bg-gray-900 border border-gray-800 rounded-xl shadow-2xl z-[100] overflow-hidden hidden';
            container.style.maxHeight = '400px';
            container.style.overflowY = 'auto';
            if(window.innerWidth < 1024 && form.closest('#mobileMenu')) {
                 container.style.position = 'static';
                 container.style.marginTop = '8px';
            }
            form.style.position = 'relative';
            form.appendChild(container);
            
            let timeout = null;
            
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                const q = this.value.trim();
                if (q.length < 2) {
                    container.classList.add('hidden');
                    return;
                }
                
                // Show loading
                container.innerHTML = `<div class="p-4 text-center text-gray-500 text-sm flex items-center justify-center gap-2"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Đang tìm...</div>`;
                container.classList.remove('hidden');
                lucide.createIcons();
                
                timeout = setTimeout(() => {
                    fetch('/api/v1/search.php?keyword=' + encodeURIComponent(q))
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success' && data.data && data.data.items && data.data.items.length > 0) {
                                let html = '<div class="py-2">';
                                const domain = data.data.APP_DOMAIN_CDN_IMAGE || data.data.domain || 'https://phimimg.com/';
                                
                                data.data.items.slice(0, 5).forEach(item => {
                                    let thumb = item.thumb_url || item.poster_url || '';
                                    if (!thumb.startsWith('http')) {
                                        thumb = domain.replace(/\/$/, '') + '/' + thumb.replace(/^\//, '');
                                    }
                                    html += `
                                        <a href="/phim/${item.slug}" class="flex items-center px-4 py-2 hover:bg-gray-800 transition-colors gap-3 group">
                                            <div class="w-10 h-14 bg-gray-800 rounded overflow-hidden flex-shrink-0 shadow">
                                                <img src="${thumb}" alt="${item.name.replace(/"/g, '&quot;')}" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-white text-sm font-medium truncate group-hover:text-red-500 transition-colors">${item.name}</div>
                                                <div class="text-gray-500 text-[11px] truncate">${item.origin_name || ''}</div>
                                            </div>
                                        </a>
                                    `;
                                });
                                html += `
                                    <a href="/search?keyword=${encodeURIComponent(q)}" class="block px-4 py-3 text-center text-sm text-red-500 hover:bg-gray-800 transition-colors font-medium border-t border-gray-800 mt-2">
                                        Xem tất cả kết quả <i data-lucide="arrow-right" class="w-3 h-3 inline-block ml-1"></i>
                                    </a>
                                </div>`;
                                container.innerHTML = html;
                                lucide.createIcons();
                            } else {
                                container.innerHTML = `<div class="p-4 text-center text-gray-500 text-sm">Không tìm thấy "${q}"</div>`;
                            }
                        })
                        .catch(e => {
                            container.classList.add('hidden');
                        });
                }, 500);
            });
            
            // Hide when clicking outside
            document.addEventListener('click', function(e) {
                if (!form.contains(e.target)) {
                    container.classList.add('hidden');
                }
            });
            
            // Show again when focusing
            input.addEventListener('focus', function() {
                if (this.value.trim().length >= 2 && container.innerHTML !== '') {
                    container.classList.remove('hidden');
                }
            });
        });
    });
    </script>
    <div class="pt-20 pb-12">
