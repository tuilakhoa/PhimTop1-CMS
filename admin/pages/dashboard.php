<?php
$movieCount = 0;
$totalViews = 0;
$pdo = getPDO();
if ($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM movies");
    if ($stmt) {
        $movieCount = $stmt->fetchColumn();
    }
    
    $stmt2 = $pdo->query("SELECT SUM(view) FROM movies");
    if ($stmt2) {
        $totalViews = (int)$stmt2->fetchColumn();
    }
}

$cfApiToken = $settings['cfApiToken'] ?? '';
$cfAccountId = $settings['cfAccountId'] ?? '';
$cfZoneId = $settings['cfZoneId'] ?? '';

$cfTurnstileData = '--';
$cfAnalyticsData = '--';
$cfApiConfigured = !empty($cfApiToken);

if ($cfApiConfigured) {
    $datetimeStart = gmdate('Y-m-d\TH:i:s\Z', strtotime('-24 hours'));
    $datetimeEnd = gmdate('Y-m-d\TH:i:s\Z');

    // Fetch Turnstile (if Account ID is set)
    if (!empty($cfAccountId)) {
        $turnstileQuery = 'query Turnstile($accountTag: string!, $start: datetime!, $end: datetime!) { viewer { accounts(filter: {accountTag: $accountTag}) { turnstileAnalyticsAdaptiveGroups(limit: 1, filter: {datetime_geq: $start, datetime_leq: $end}) { sum { interactiveSolve } } } } }';
        $variables = ['accountTag' => $cfAccountId, 'start' => $datetimeStart, 'end' => $datetimeEnd];
        
        $ch = curl_init('https://api.cloudflare.com/client/v4/graphql');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['query' => $turnstileQuery, 'variables' => $variables]),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $cfApiToken, 'Content-Type: application/json'],
            CURLOPT_TIMEOUT => 3
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($res, true);
        if (isset($data['data']['viewer']['accounts'][0]['turnstileAnalyticsAdaptiveGroups'][0]['sum']['interactiveSolve'])) {
            $cfTurnstileData = number_format($data['data']['viewer']['accounts'][0]['turnstileAnalyticsAdaptiveGroups'][0]['sum']['interactiveSolve']);
        } elseif (isset($data['errors'])) {
            $cfTurnstileData = 'Lỗi API';
        } else {
            $cfTurnstileData = '0';
        }
    }

    // Fetch Web Analytics (if Zone ID is set)
    if (!empty($cfZoneId)) {
        $analyticsQuery = 'query Analytics($zoneTag: string!, $start: datetime!, $end: datetime!) { viewer { zones(filter: {zoneTag: $zoneTag}) { httpRequestsAdaptiveGroups(limit: 1, filter: {datetime_geq: $start, datetime_leq: $end}) { sum { visits } } } } }';
        $variables = ['zoneTag' => $cfZoneId, 'start' => $datetimeStart, 'end' => $datetimeEnd];
        
        $ch = curl_init('https://api.cloudflare.com/client/v4/graphql');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['query' => $analyticsQuery, 'variables' => $variables]),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $cfApiToken, 'Content-Type: application/json'],
            CURLOPT_TIMEOUT => 3
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($res, true);
        if (isset($data['data']['viewer']['zones'][0]['httpRequestsAdaptiveGroups'][0]['sum']['visits'])) {
            $cfAnalyticsData = number_format($data['data']['viewer']['zones'][0]['httpRequestsAdaptiveGroups'][0]['sum']['visits']);
        } elseif (isset($data['errors'])) {
            $cfAnalyticsData = 'Lỗi API';
        } else {
            $cfAnalyticsData = '0';
        }
    }
}
?>
<h2 class="text-2xl font-bold text-white mb-6">Tổng Quan Hệ Thống</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 transition-transform transform hover:-translate-y-1 hover:shadow-lg">
        <div class="flex justify-between items-start">
            <div><p class="text-gray-400 mb-1">Chế Độ Hiển Thị</p><h3 class="text-xl font-bold text-white uppercase"><?= htmlspecialchars($settings['displayMode']) ?></h3></div>
            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-500"><i data-lucide="monitor" class="w-5 h-5"></i></div>
        </div>
    </div>
    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 transition-transform transform hover:-translate-y-1 hover:shadow-lg">
        <div class="flex justify-between items-start">
            <div><p class="text-gray-400 mb-1">Theme Hiện Tại</p><h3 class="text-xl font-bold text-white capitalize"><?= htmlspecialchars($settings['theme']) ?></h3></div>
            <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center text-purple-500"><i data-lucide="palette" class="w-5 h-5"></i></div>
        </div>
    </div>
    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 transition-transform transform hover:-translate-y-1 hover:shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-400 mb-1">Tổng Số Phim</p>
                <h3 class="text-xl font-bold text-white"><?= number_format($movieCount) ?></h3>
            </div>
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center text-green-500"><i data-lucide="film" class="w-5 h-5"></i></div>
        </div>
    </div>
</div>

<h3 class="text-xl font-bold text-white mb-4 flex items-center"><i data-lucide="shield-check" class="w-6 h-6 mr-2 text-green-500"></i> Bảo Mật & Đo Lường</h3>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Views -->
    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 transition-transform transform hover:-translate-y-1 hover:shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-400 mb-1">Lượt Xem Hệ Thống</p>
                <h3 class="text-xl font-bold text-white"><?= number_format($totalViews) ?></h3>
                <p class="text-xs text-gray-500 mt-1">Tổng từ CSDL nội bộ</p>
            </div>
            <div class="w-10 h-10 bg-indigo-500/10 rounded-lg flex items-center justify-center text-indigo-500"><i data-lucide="eye" class="w-5 h-5"></i></div>
        </div>
    </div>

    <!-- Turnstile -->
    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 transition-transform transform hover:-translate-y-1 hover:shadow-lg">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-gray-400 mb-1">CF Turnstile</p>
                <h3 class="text-sm font-medium text-white">Lượt Chặn Bot</h3>
            </div>
            <div class="w-10 h-10 bg-orange-500/10 rounded-lg flex items-center justify-center text-orange-500"><i data-lucide="shield-alert" class="w-5 h-5"></i></div>
        </div>
        <div class="flex items-end justify-between">
            <h2 class="text-3xl font-bold text-white"><?= $cfTurnstileData ?></h2>
            <?php if (!$cfApiConfigured || empty($cfAccountId)): ?>
                <span class="text-xs text-orange-400 bg-orange-400/10 px-2 py-1 rounded">Cần Account ID</span>
            <?php else: ?>
                <span class="text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded">Đã Kết Nối</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- CF Analytics -->
    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 transition-transform transform hover:-translate-y-1 hover:shadow-lg">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-gray-400 mb-1">CF Web Analytics</p>
                <h3 class="text-sm font-medium text-white">Khách Truy Cập (24h)</h3>
            </div>
            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-500"><i data-lucide="users" class="w-5 h-5"></i></div>
        </div>
        <div class="flex items-end justify-between">
            <h2 class="text-3xl font-bold text-white"><?= $cfAnalyticsData ?></h2>
            <?php if (!$cfApiConfigured || empty($cfZoneId)): ?>
                <span class="text-xs text-blue-400 bg-blue-400/10 px-2 py-1 rounded">Cần Zone ID</span>
            <?php else: ?>
                <span class="text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded">Đã Kết Nối</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- GA -->
    <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 transition-transform transform hover:-translate-y-1 hover:shadow-lg">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-gray-400 mb-1">Google Analytics</p>
                <h3 class="text-sm font-medium text-white">Đang Hoạt Động</h3>
            </div>
            <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center text-yellow-500"><i data-lucide="activity" class="w-5 h-5"></i></div>
        </div>
        <div class="flex items-end justify-between">
            <h2 class="text-3xl font-bold text-white">--</h2>
            <span class="text-xs text-yellow-400 bg-yellow-400/10 px-2 py-1 rounded">Cần Service Account</span>
        </div>
    </div>
</div>

<div class="bg-gradient-to-r from-gray-900 to-gray-800 p-8 rounded-2xl border border-gray-700 shadow-2xl relative overflow-hidden">
    <div class="absolute top-[-20%] right-[-10%] w-64 h-64 bg-red-600/10 rounded-full blur-[80px] pointer-events-none"></div>
    <h3 class="text-xl font-bold text-white mb-4">✨ PhimTop1 CMS v1.1 - Nhanh Hơn, Nhẹ Hơn, Chuẩn SEO Hơn</h3>
    <ul class="space-y-3 text-gray-300">
        <li class="flex items-center"><i data-lucide="zap" class="w-5 h-5 mr-3 text-yellow-400"></i> Được viết hoàn toàn bằng <strong>PHP Thuần (Monolithic)</strong>, siêu nhẹ, không cần Node.js, không cần npm.</li>
        <li class="flex items-center"><i data-lucide="search" class="w-5 h-5 mr-3 text-blue-400"></i> Hỗ trợ URL Rewrite loại bỏ đuôi <code>.php</code>, giúp tối ưu hóa SEO tối đa cho Website.</li>
        <li class="flex items-center"><i data-lucide="image" class="w-5 h-5 mr-3 text-green-400"></i> Tích hợp tính năng tải Logo & Favicon dễ dàng ngay trong trang quản trị.</li>
        <li class="flex items-center"><i data-lucide="layout-template" class="w-5 h-5 mr-3 text-purple-400"></i> Kiến trúc Admin Module giúp mở rộng tính năng dễ dàng.</li>
    </ul>
</div>
