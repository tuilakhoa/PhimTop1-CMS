<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$settings = getSettings();
$pageTitle = "Phim đang theo dõi - " . ($settings['siteName'] ?? 'PhimTop1');
$userEmail = $_SESSION['user']['email'];

// Handle clear bookmark via POST (if requested here instead of API)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_follows') {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("DELETE FROM user_follows WHERE user_email = ?");
        $stmt->execute([$userEmail]);
        header('Location: /bookmark.php?cleared=1');
        exit;
    }
}

// Fetch bookmark from database directly since we are on the same server
$bookmarkItems = [];
$pdo = getPDO();
if ($pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM user_follows 
        WHERE user_email = ? 
        ORDER BY updated_at DESC 
        LIMIT 100
    ");
    $stmt->execute([$userEmail]);
    $bookmarkItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Render Header using theme
include "themes/{$settings['theme']}/header.php";
?>

<div class="container mx-auto px-4 py-8 max-w-6xl min-h-[60vh]">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-white flex items-center">
            <i data-lucide="bookmark" class="w-8 h-8 mr-3 text-red-500"></i> Phim Đang Theo Dõi
        </h1>
        <?php if (!empty($bookmarkItems)): ?>
        <form method="POST" action="/bookmark.php" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ lịch sử xem phim không? Hành động này không thể hoàn tác.');">
            <input type="hidden" name="action" value="clear_follows">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Xóa Lịch Sử
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['cleared'])): ?>
    <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-lg mb-6 flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> Phim đang theo dõi đã được xóa thành công.
    </div>
    <?php endif; ?>

    <?php if (empty($bookmarkItems)): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
            <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="clock" class="w-10 h-10 text-gray-500"></i>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Chưa có phim nào</h2>
            <p class="text-gray-400 mb-6">Bạn chưa lưu bộ phim nào. Hãy khám phá kho phim khổng lồ của chúng tôi!</p>
            <a href="/" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-full transition-colors">
                Xem Phim Ngay
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($bookmarkItems as $item): 
                $slug = $item['item_slug'];
                $thumbUrl = $item['thumb_url'] ?? '';
                if (empty($thumbUrl)) {
                    $thumbUrl = 'https://ui-avatars.com/api/?name=' . urlencode($item['item_name']) . '&background=random';
                } else if (strpos($thumbUrl, 'http') !== 0) {
                    $thumbUrl = 'https://phimimg.com/' . ltrim($thumbUrl, '/');
                }
            ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-900 border border-gray-800 transition-all hover:scale-[1.02] hover:shadow-xl hover:shadow-red-500/10">
                    <div class="relative aspect-video w-full overflow-hidden bg-black">
                        <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70 group-hover:opacity-100">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
                        <div class="absolute bottom-2 left-2 bg-phim-yellow text-black text-xs font-bold px-2 py-1 rounded shadow-sm flex items-center">
                            <i data-lucide="bookmark" class="w-3 h-3 mr-1"></i> Đã lưu
                        </div>
                    </div>
                    <div class="p-4 relative z-10 flex flex-col flex-grow">
                        <h3 class="text-base font-bold text-white line-clamp-1 mb-1 group-hover:text-red-400 transition-colors" title="<?= htmlspecialchars($item['item_name']) ?>">
                            <?= htmlspecialchars($item['item_name']) ?>
                        </h3>
                        <p class="text-xs text-gray-400 flex items-center mt-auto pt-2">
                            <i data-lucide="calendar" class="w-3 h-3 mr-1"></i> 
                            Lưu lúc: <?= date('d/m/Y H:i', strtotime($item['updated_at'])) ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "themes/{$settings['theme']}/footer.php"; ?>
