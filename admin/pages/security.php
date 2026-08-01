<h2 class="text-2xl font-bold text-white mb-6">Bảo Mật & Đo Lường (Cloudflare)</h2>

<form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-2xl">
    <input type="hidden" name="action" value="update_settings">
    
    <!-- Bảo mật Cloudflare Turnstile -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center border-b border-gray-800 pb-2">
            <i data-lucide="shield-alert" class="w-5 h-5 mr-2 text-red-500"></i> Cloudflare Turnstile (Chống Bot)
        </h3>
        
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Site Key</label>
                <input type="text" name="cfTurnstileKey" value="<?= htmlspecialchars($settings['cfTurnstileKey'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none" placeholder="1x00000000000000000000AA">
                <p class="text-xs text-gray-500 mt-1">Mã khóa trang web được cấp bởi Cloudflare.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Secret Key</label>
                <input type="password" name="cfTurnstileSecret" value="<?= htmlspecialchars($settings['cfTurnstileSecret'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-red-500 outline-none" placeholder="1x0000000000000000000000000000000AA">
                <p class="text-xs text-gray-500 mt-1">Mã bí mật dùng để xác thực ở backend (không bắt buộc nếu chỉ hiện captcha ở frontend).</p>
            </div>
        </div>
    </div>

    <!-- Đo lường Cloudflare Web Analytics -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center border-b border-gray-800 pb-2">
            <i data-lucide="bar-chart-2" class="w-5 h-5 mr-2 text-blue-500"></i> Cloudflare Web Analytics
        </h3>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Analytics Token</label>
            <input type="text" name="cfAnalyticsToken" value="<?= htmlspecialchars($settings['cfAnalyticsToken'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-blue-500 outline-none" placeholder="Vd: 8b0...a1c">
            <p class="text-xs text-gray-500 mt-2">Dùng để phân tích lưu lượng truy cập mà không dùng Cookie, bảo vệ quyền riêng tư người dùng.</p>
        </div>
    </div>

    <!-- Tích hợp Cloudflare API (Backend) -->
    <div class="mb-8 bg-blue-500/5 border border-blue-500/20 rounded-xl p-5">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center border-b border-gray-800 pb-2">
            <i data-lucide="cloud" class="w-5 h-5 mr-2 text-indigo-500"></i> Tích hợp Cloudflare API
        </h3>
        <p class="text-sm text-gray-400 mb-5">Cấu hình API Token để tự động lấy số liệu thống kê hiển thị trên màn hình Tổng quan (Dashboard).</p>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">API Token (Quyền Read Analytics)</label>
                <input type="password" name="cfApiToken" value="<?= htmlspecialchars($settings['cfApiToken'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-indigo-500 outline-none" placeholder="Vd: pXXXXXX_YYYYYY...">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Account ID</label>
                    <input type="text" name="cfAccountId" value="<?= htmlspecialchars($settings['cfAccountId'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-indigo-500 outline-none" placeholder="Vd: 3a1b2c...">
                    <p class="text-xs text-gray-500 mt-1">Dùng để lấy dữ liệu Turnstile.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Zone ID</label>
                    <input type="text" name="cfZoneId" value="<?= htmlspecialchars($settings['cfZoneId'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-indigo-500 outline-none" placeholder="Vd: 9f8e7d...">
                    <p class="text-xs text-gray-500 mt-1">Dùng để lấy Web Analytics.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Analytics (GA4) -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center border-b border-gray-800 pb-2">
            <i data-lucide="pie-chart" class="w-5 h-5 mr-2 text-green-500"></i> Google Analytics (GA4)
        </h3>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Measurement ID (Mã theo dõi)</label>
            <input type="text" name="gaMeasurementId" value="<?= htmlspecialchars($settings['gaMeasurementId'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-green-500 outline-none" placeholder="G-XXXXXXXXXX">
            <p class="text-xs text-gray-500 mt-2">Ví dụ: G-123456789. Hệ thống sẽ tự động chèn mã gtag.js độc lập.</p>
        </div>
    </div>

    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg transition-colors flex items-center shadow-lg shadow-red-500/20">
        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình
    </button>
</form>
