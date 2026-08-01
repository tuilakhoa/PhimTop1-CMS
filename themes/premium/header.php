<?php
$settings = getSettings();
$seoTitle = $pageTitle ?? ($settings['seoTitle'] ?? 'PhimTop1 - Premium Theme');
$seoDesc = $pageDesc ?? ($settings['seoDesc'] ?? 'Hệ thống xem phim trực tuyến chất lượng cao');
$seoKeywords = $pageKeywords ?? ($settings['seoKeywords'] ?? 'xem phim, phim online');
$siteName = $settings['siteName'] ?? 'PhimTop1';

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
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Premium CSS -->
    <link rel="stylesheet" href="/themes/premium/assets/css/style.css?v=<?= time() ?>">
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="container nav-container">
            <a href="/" class="logo">
                <?php if (!empty($settings['logoUrl'])): ?>
                    <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" style="height: 32px;">
                <?php else: ?>
                    <i data-lucide="play-circle" style="color: var(--color-primary); width: 32px; height: 32px;"></i>
                    <?= htmlspecialchars($siteName) ?><span>.</span>
                <?php endif; ?>
            </a>
            
            <div class="nav-links">
                <a href="/" class="active">Trang Chủ</a>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le">Phim Lẻ</a>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-bo">Phim Bộ</a>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-chieu-rap">Chiếu Rạp</a>
            </div>
            
            <div class="nav-actions" style="display: flex; gap: 20px; align-items: center;">
                <form action="/search" method="GET" class="search-box">
                    <i data-lucide="search" class="search-icon"></i>
                    <input type="text" name="keyword" class="search-input" placeholder="Tìm kiếm phim...">
                </form>
                <?php include __DIR__ . '/../../includes/user_nav.php'; ?>
            </div>
        </div>
    </nav>
    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>
