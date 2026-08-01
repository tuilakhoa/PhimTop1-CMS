<?php
$settings = getSettings();
$bodyClass = 'bg-[#0f0f0f] text-gray-200 antialiased font-sans'; // Changed to match Netflix/PhimhayOK dark gray

if (!function_exists('getPhimImgUrl')) {
    function getPhimImgUrl($url) {
        global $data, $settings;
        if (empty($url)) return '';
        if (preg_match('/^http/', $url)) return $url;
        
        // If the URL looks like a TMDB image path (starts with / and has alphanumeric chars + ext)
        if (preg_match('/^\/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/i', $url)) {
            // It's a TMDB image. We can serve it directly from TMDB's image CDN.
            return 'https://image.tmdb.org/t/p/w500' . $url;
        }

        $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
        return rtrim($domain, '/') . '/' . ltrim($url, '/');
    }
}

$bodyClass = 'bg-[#0f0f0f] text-gray-200 antialiased font-sans'; // Changed to match Netflix/PhimhayOK dark gray

global $pageTitle, $pageDesc, $pageKeywords;
$seoTitle = $pageTitle ?? ($settings['seoTitle'] ?? 'PhimHayOK - Xem Phim Online');
$seoDesc = $pageDesc ?? ($settings['seoDesc'] ?? 'Hệ thống xem phim trực tuyến chất lượng cao');
$seoKeywords = $pageKeywords ?? ($settings['seoKeywords'] ?? 'xem phim, phim online');
$siteName = 'PhimHayOK';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($seoTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDesc) ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'phim-yellow': '#eab308', // Yellow matching the logo
                        'phim-bg': '#0f0f0f',
                        'phim-card': '#1a1a1a'
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="/assets/js/main.js?v=<?= time() ?>"></script>
    
    <link rel="stylesheet" href="/themes/phimhayok/assets/css/style.css?v=<?= time() ?>">
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

</head>
<body class="<?= $bodyClass ?> min-h-screen flex flex-col">
    <!-- Header -->
    <nav class="header-solid fixed w-full top-0 z-50 transition-all duration-300 h-[72px]">
        <div class="px-4 md:px-6 lg:px-12 w-full h-full flex items-center justify-between">
            
            <!-- Left: Logo & Nav -->
            <div class="flex items-center xl:space-x-8">
                <!-- Logo -->
                <a href="/" class="flex items-center shrink-0 mr-4 xl:mr-0">
                    <?php if (!empty($settings['logoUrl'])): ?>
                        <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="<?= htmlspecialchars($settings['siteName'] ?? 'Logo') ?>" class="h-8 md:h-10 object-contain">
                    <?php else: ?>
                        <div class="flex items-stretch font-black text-xl italic tracking-tighter">
                            <div class="bg-phim-yellow text-black px-2 py-1 flex items-center">
                                <?= htmlspecialchars(strtoupper($settings['siteName'] ?? 'PHIM')) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </a>
                
                <!-- Desktop Nav -->
                <div class="hidden xl:flex items-center space-x-6 text-sm font-semibold text-gray-300">
                    <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo" class="hover:text-white flex items-center transition-colors">
                        <i data-lucide="tv" class="w-4 h-4 mr-1.5"></i> Phim bộ
                    </a>
                    <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="hover:text-white flex items-center transition-colors">
                        <i data-lucide="film" class="w-4 h-4 mr-1.5"></i> Phim lẻ
                    </a>
                    <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-ngan" class="hover:text-white flex items-center transition-colors">
                        <i data-lucide="smartphone" class="w-4 h-4 mr-1.5"></i> Phim ngắn
                    </a>
                    
                    <!-- Dropdowns -->
                    <div class="relative group">
                        <button class="hover:text-white flex items-center transition-colors py-6">
                            Thể loại <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div class="absolute top-[100%] left-0 w-[500px] bg-[#141414] border border-gray-800 rounded-lg shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-5 grid grid-cols-3 gap-3">
                            <?php foreach ($genres as $g): ?>
                                <a href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/<?= htmlspecialchars($g['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded transition-colors truncate"><?= htmlspecialchars($g['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="relative group">
                        <button class="hover:text-white flex items-center transition-colors py-6">
                            Quốc gia <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div class="absolute top-[100%] left-0 w-[500px] bg-[#141414] border border-gray-800 rounded-lg shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-5 grid grid-cols-3 gap-3">
                            <?php foreach ($countries as $c): ?>
                                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/<?= htmlspecialchars($c['slug']) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded transition-colors truncate"><?= htmlspecialchars($c['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="relative group">
                        <button class="hover:text-white flex items-center transition-colors py-6">
                            Năm <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div class="absolute top-[100%] left-0 w-[300px] bg-[#141414] border border-gray-800 rounded-lg shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-4 grid grid-cols-4 gap-2">
                            <?php for($y = date('Y'); $y >= 2010; $y--): ?>
                                <a href="/nam/<?= $y ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded text-center transition-colors"><?= $y ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right: Search & Login -->
            <div class="flex items-center space-x-4 shrink-0">
                <form action="/search" method="GET" class="relative hidden md:block">
                    <input type="text" name="keyword" placeholder="Tìm kiếm phim, tác giả..." 
                        class="bg-[#1f1f1f] text-gray-200 text-sm rounded-full pl-5 pr-10 py-2.5 focus:outline-none focus:ring-1 focus:ring-gray-600 border border-transparent w-[300px] placeholder-gray-500 font-medium">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-white">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>
                </form>
                
                <a href="/login" class="bg-phim-yellow hover:bg-yellow-400 text-black font-bold text-sm px-6 py-2.5 rounded transition-colors uppercase shadow-[0_0_15px_rgba(234,179,8,0.2)]">
                    Đăng Nhập
                </a>

                <?php if (isset($_SESSION['admin'])): ?>
                    <a href="/admin" class="hidden md:flex text-gray-400 hover:text-white items-center text-sm ml-2">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                    </a>
                <?php endif; ?>

                <?php include __DIR__ . '/../../includes/user_nav.php'; ?>

                <button id="mobileMenuBtn" class="xl:hidden text-white focus:outline-none ml-2">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu (Hidden) -->
        <div id="mobileMenu" class="hidden bg-[#0a0a0a] border-t border-gray-900 absolute w-full left-0 top-[72px] shadow-2xl pb-4">
            <!-- (Mobile menu implementation kept simple) -->
        </div>
    </nav>
    <main class="flex-grow pt-[72px] bg-black">
