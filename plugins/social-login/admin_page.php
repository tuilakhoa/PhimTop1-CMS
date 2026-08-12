<?php
$settings = getSettings();
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_social_login') {
    $updates = [];
    $updates['enableGoogleLogin'] = (int)($_POST['enableGoogleLogin'] ?? 0);
    $updates['googleClientId'] = trim($_POST['googleClientId'] ?? '');
    $updates['googleClientSecret'] = trim($_POST['googleClientSecret'] ?? '');
    
    $updates['enableMicrosoftLogin'] = (int)($_POST['enableMicrosoftLogin'] ?? 0);
    $updates['msClientId'] = trim($_POST['msClientId'] ?? '');
    $updates['msClientSecret'] = trim($_POST['msClientSecret'] ?? '');
    $updates['msTenantId'] = trim($_POST['msTenantId'] ?? 'common');

    updateSettings($updates);
    $settings = getSettings();
    $successMsg = "Lưu cấu hình Social Login thành công!";
}
?>
<h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
    <i data-lucide="shield-check" class="w-6 h-6 text-red-500"></i> Cấu Hình Đăng Nhập MXH
</h2>

<?php if ($successMsg): ?>
    <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl flex items-center shadow-lg">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
        <?= htmlspecialchars($successMsg) ?>
    </div>
<?php endif; ?>

<form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:p-8 shadow-xl max-w-4xl">
    <input type="hidden" name="action" value="save_social_login">
    
    <!-- Google Auth -->
    <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2 flex items-center mt-4">
        <i data-lucide="chrome" class="w-5 h-5 mr-2 text-red-500"></i> Google OAuth 2.0
    </h3>
    <div class="mb-5 bg-blue-500/10 border border-blue-500/30 p-4 rounded-xl flex items-start space-x-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm text-blue-300 font-medium mb-1">Authorized redirect URI (Frontend):</p>
            <code class="bg-gray-900 border border-gray-700 px-3 py-1.5 rounded-lg select-all text-xs font-mono text-gray-300 inline-block mb-2"><?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/api/auth.php?action=google_callback' ?></code>
            
            <p class="text-sm text-blue-300 font-medium mb-1 mt-2">Authorized redirect URI (Admin Login):</p>
            <code class="bg-gray-900 border border-gray-700 px-3 py-1.5 rounded-lg select-all text-xs font-mono text-gray-300 inline-block"><?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=google_callback' ?></code>
            
            <p class="text-xs text-blue-400/80 mt-2">Copy các đường dẫn này dán vào cấu hình OAuth 2.0 Client ID trên Google Cloud Console.</p>
        </div>
    </div>
    
    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Trạng thái Google Login</label>
        <select name="enableGoogleLogin" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
            <option value="1" <?= (isset($settings['enableGoogleLogin']) && $settings['enableGoogleLogin'] == 1) ? 'selected' : '' ?>>Bật</option>
            <option value="0" <?= (!isset($settings['enableGoogleLogin']) || $settings['enableGoogleLogin'] == 0) ? 'selected' : '' ?>>Tắt</option>
        </select>
    </div>

    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Google Client ID</label>
        <input type="text" name="googleClientId" value="<?= htmlspecialchars($settings['googleClientId'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow" placeholder="Nhập Client ID...">
    </div>
    <div class="mb-8">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Google Client Secret</label>
        <input type="password" name="googleClientSecret" value="<?= htmlspecialchars($settings['googleClientSecret'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none transition-shadow" placeholder="Nhập Client Secret...">
    </div>

    <!-- Microsoft Auth -->
    <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2 flex items-center mt-8">
        <i data-lucide="layout-grid" class="w-5 h-5 mr-2 text-blue-500"></i> Microsoft OAuth 2.0
    </h3>
    <div class="mb-5 bg-blue-500/10 border border-blue-500/30 p-4 rounded-xl flex items-start space-x-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm text-blue-300 font-medium mb-1">Web Redirect URI (Frontend):</p>
            <code class="bg-gray-900 border border-gray-700 px-3 py-1.5 rounded-lg select-all text-xs font-mono text-gray-300 inline-block mb-2"><?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/api/auth.php?action=microsoft_callback' ?></code>
            
            <p class="text-sm text-blue-300 font-medium mb-1 mt-2">Web Redirect URI (Admin Login):</p>
            <code class="bg-gray-900 border border-gray-700 px-3 py-1.5 rounded-lg select-all text-xs font-mono text-gray-300 inline-block"><?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=microsoft_callback' ?></code>
            
            <p class="text-xs text-blue-400/80 mt-2">Copy các đường dẫn này dán vào Web Redirect URI trên Azure Portal.</p>
        </div>
    </div>
    
    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Trạng thái Microsoft Login</label>
        <select name="enableMicrosoftLogin" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none transition-shadow">
            <option value="1" <?= (isset($settings['enableMicrosoftLogin']) && $settings['enableMicrosoftLogin'] == 1) ? 'selected' : '' ?>>Bật</option>
            <option value="0" <?= (!isset($settings['enableMicrosoftLogin']) || $settings['enableMicrosoftLogin'] == 0) ? 'selected' : '' ?>>Tắt</option>
        </select>
    </div>

    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Microsoft Client ID</label>
        <input type="text" name="msClientId" value="<?= htmlspecialchars($settings['msClientId'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none transition-shadow" placeholder="Nhập Client ID...">
    </div>
    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Microsoft Client Secret</label>
        <input type="password" name="msClientSecret" value="<?= htmlspecialchars($settings['msClientSecret'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none transition-shadow" placeholder="Nhập Client Secret...">
    </div>
    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Microsoft Tenant ID</label>
        <input type="text" name="msTenantId" value="<?= htmlspecialchars($settings['msTenantId'] ?? 'common') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none transition-shadow" placeholder="Mặc định: common">
        <p class="text-xs text-gray-500 mt-2">Nhập `common` nếu cho phép mọi tài khoản, hoặc nhập ID tổ chức của bạn.</p>
    </div>

    <div class="mt-8 flex justify-end border-t border-gray-800 pt-6">
        <button type="submit" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg shadow-red-500/25 flex items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Lưu Thay Đổi
        </button>
    </div>
</form>
