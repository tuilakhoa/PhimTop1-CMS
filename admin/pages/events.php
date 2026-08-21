<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
$pdo = getPDO();

$settings = getSettings();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['enable_watch_reward'] = isset($_POST['enable_watch_reward']) ? 1 : 0;
    $settings['watch_reward_interval'] = (int)$_POST['watch_reward_interval'];
    $settings['watch_reward_coins'] = (int)$_POST['watch_reward_coins'];
    
    $settings['enable_daily_checkin'] = isset($_POST['enable_daily_checkin']) ? 1 : 0;
    $settings['daily_checkin_coins'] = (int)$_POST['daily_checkin_coins'];
    $settings['daily_checkin_streak_bonus'] = (int)$_POST['daily_checkin_streak_bonus'];

    if (saveSettings($settings)) {
        $message = "Đã lưu cài đặt sự kiện thành công!";
    } else {
        $error = "Có lỗi xảy ra khi lưu cài đặt.";
    }
}

// Defaults
$enable_watch_reward = isset($settings['enable_watch_reward']) ? $settings['enable_watch_reward'] : 1;
$watch_reward_interval = isset($settings['watch_reward_interval']) ? $settings['watch_reward_interval'] : 1;
$watch_reward_coins = isset($settings['watch_reward_coins']) ? $settings['watch_reward_coins'] : 1;

$enable_daily_checkin = isset($settings['enable_daily_checkin']) ? $settings['enable_daily_checkin'] : 1;
$daily_checkin_coins = isset($settings['daily_checkin_coins']) ? $settings['daily_checkin_coins'] : 20;
$daily_checkin_streak_bonus = isset($settings['daily_checkin_streak_bonus']) ? $settings['daily_checkin_streak_bonus'] : 50;

// Ensure database schema is ready
if ($pdo) {
    try { $pdo->exec("ALTER TABLE members ADD COLUMN last_reward_time TIMESTAMP NULL DEFAULT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE members ADD COLUMN last_checkin DATE NULL DEFAULT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE members ADD COLUMN checkin_streak INT DEFAULT 0"); } catch (PDOException $e) {}
}
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-white mb-1">Quản Lý Sự Kiện & Thưởng Xu</h1>
        <p class="text-gray-400 text-sm">Cấu hình các sự kiện giúp giữ chân người dùng (Gamification).</p>
    </div>
</div>

<?php if ($message): ?>
<div class="bg-green-600/20 border border-green-500 text-green-400 p-4 rounded-xl mb-6 flex items-center">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-600/20 border border-red-500 text-red-400 p-4 rounded-xl mb-6 flex items-center">
    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Daily Check-in Settings -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center mr-3">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-blue-500"></i>
                </div>
                <h2 class="text-lg font-bold text-white">Điểm Danh Hàng Ngày</h2>
            </div>
            <p class="text-gray-400 text-sm mb-6">Thưởng Xu cho người dùng khi họ mở app mỗi ngày.</p>
            
            <div class="space-y-4">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="enable_daily_checkin" value="1" <?= $enable_daily_checkin ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-700 bg-gray-800 text-red-600 focus:ring-red-600 focus:ring-offset-gray-900">
                    <span class="text-white font-medium">Bật tính năng Điểm Danh</span>
                </label>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Số Xu thưởng mỗi ngày</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="coins" class="w-5 h-5 text-yellow-500"></i>
                        </div>
                        <input type="number" name="daily_checkin_coins" value="<?= htmlspecialchars($daily_checkin_coins) ?>" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors" min="0">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Thưởng thêm khi đạt chuỗi 7 ngày</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="gift" class="w-5 h-5 text-red-500"></i>
                        </div>
                        <input type="number" name="daily_checkin_streak_bonus" value="<?= htmlspecialchars($daily_checkin_streak_bonus) ?>" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors" min="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Watch to Earn Settings -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-lg bg-green-600/20 flex items-center justify-center mr-3">
                    <i data-lucide="play-circle" class="w-5 h-5 text-green-500"></i>
                </div>
                <h2 class="text-lg font-bold text-white">Xem Phim Nhận Xu (Watch to Earn)</h2>
            </div>
            <p class="text-gray-400 text-sm mb-6">Thưởng Xu tự động khi người dùng xem phim (dựa trên Ping lịch sử).</p>
            
            <div class="space-y-4">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="enable_watch_reward" value="1" <?= $enable_watch_reward ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-700 bg-gray-800 text-red-600 focus:ring-red-600 focus:ring-offset-gray-900">
                    <span class="text-white font-medium">Bật tính năng Xem Phim Nhận Xu</span>
                </label>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Số phút xem cần thiết</label>
                        <input type="number" name="watch_reward_interval" value="<?= htmlspecialchars($watch_reward_interval) ?>" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors" min="1">
                        <p class="text-xs text-gray-500 mt-1">VD: 1 phút</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Số Xu thưởng tương ứng</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="coins" class="w-4 h-4 text-yellow-500"></i>
                            </div>
                            <input type="number" name="watch_reward_coins" value="<?= htmlspecialchars($watch_reward_coins) ?>" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-9 pr-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors" min="1">
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-800 p-3 rounded-lg border border-gray-700">
                    <p class="text-sm text-gray-300">
                        <i data-lucide="info" class="w-4 h-4 inline-block mr-1 text-gray-400 mb-1"></i>
                        Với cấu hình hiện tại: Khách xem <b><?= $watch_reward_interval ?> phút</b> sẽ nhận được <b><?= $watch_reward_coins ?> Xu</b>. 
                        Tính năng này hoạt động dựa trên lúc app gửi lịch sử xem (thường mỗi 15-60 giây).
                    </p>
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="flex justify-end">
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl font-medium transition-colors flex items-center">
            <i data-lucide="save" class="w-5 h-5 mr-2"></i> Lưu Cấu Hình
        </button>
    </div>
</form>
