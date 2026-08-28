<?php
$pdo = getPDO();
$settings = getSettings();
$currentTheme = $settings['theme'] ?? 'phimhayok';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'activate_theme') {
    $themeSlug = $_POST['theme_slug'] ?? '';
    
    // Validate theme exists
    if (!empty($themeSlug) && is_dir(__DIR__ . '/../../themes/' . $themeSlug)) {
        updateSettings(['theme' => $themeSlug]);
        $currentTheme = $themeSlug;
        $success = "Đã kích hoạt giao diện: " . htmlspecialchars($themeSlug);
    } else {
        $error = "Giao diện không tồn tại!";
    }
}

// Get all themes
$themesDir = __DIR__ . '/../../themes/';
$themes = [];
if (is_dir($themesDir)) {
    $dirs = scandir($themesDir);
    foreach ($dirs as $dir) {
        if ($dir !== '.' && $dir !== '..' && is_dir($themesDir . $dir)) {
            $themes[] = [
                'slug' => $dir,
                'name' => ucfirst($dir),
                'preview' => file_exists($themesDir . $dir . '/preview.jpg') ? '/themes/' . $dir . '/preview.jpg' : '/assets/no-preview.jpg',
                'active' => ($dir === $currentTheme)
            ];
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-white">Quản Lý Giao Diện</h2>
</div>

<?php if (isset($success)): ?>
    <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg mb-6 flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= $success ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6 flex items-center">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= $error ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($themes as $theme): ?>
        <div class="bg-gray-900 rounded-2xl border <?= $theme['active'] ? 'border-red-500 shadow-lg shadow-red-500/20' : 'border-gray-800' ?> overflow-hidden flex flex-col transition-all hover:border-gray-600 group">
            <div class="aspect-video w-full bg-gray-800 relative overflow-hidden">
                <img src="<?= htmlspecialchars($theme['preview']) ?>" alt="<?= htmlspecialchars($theme['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <?php if ($theme['active']): ?>
                    <div class="absolute top-3 right-3 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg flex items-center">
                        <i data-lucide="check" class="w-3 h-3 mr-1"></i> Đang Dùng
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="p-5 flex flex-col flex-grow">
                <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($theme['name']) ?> Theme</h3>
                <p class="text-gray-400 text-sm mb-4">Giao diện <?= htmlspecialchars($theme['slug']) ?> mượt mà, tối ưu SEO và tốc độ.</p>
                
                <div class="mt-auto pt-4 border-t border-gray-800">
                    <?php if (!$theme['active']): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="activate_theme">
                            <input type="hidden" name="theme_slug" value="<?= htmlspecialchars($theme['slug']) ?>">
                            <button type="submit" class="w-full bg-gray-800 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-xl transition-colors flex items-center justify-center group-hover:bg-red-600">
                                <i data-lucide="monitor" class="w-4 h-4 mr-2"></i> Kích hoạt
                            </button>
                        </form>
                    <?php else: ?>
                        <button disabled class="w-full bg-red-600/20 text-red-400 font-semibold py-2 px-4 rounded-xl cursor-not-allowed flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Đang Kích Hoạt
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
