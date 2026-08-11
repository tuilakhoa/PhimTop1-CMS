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

// Fetch Google Analytics Realtime Active Users
$gaRealtimeData = '--';
$gaPropertyId = $settings['gaPropertyId'] ?? '';
$dbConfig = getDbConfig();
$serviceAccount = $dbConfig['serviceAccount'] ?? [];
$gaConfigured = !empty($gaPropertyId) && !empty($serviceAccount['private_key']);

if ($gaConfigured) {
    require_once __DIR__ . '/../includes/ga_helper.php';
    $gaAccessToken = getGaAccessToken($serviceAccount);
    if ($gaAccessToken) {
        $realtimeUsers = getGaRealtimeUsers($gaPropertyId, $gaAccessToken);
        if ($realtimeUsers !== false) {
            $gaRealtimeData = number_format((int)$realtimeUsers);
        } else {
            $gaRealtimeData = 'Lỗi API';
        }
    } else {
        $gaRealtimeData = 'Lỗi Khóa';
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
                <h3 class="text-sm font-medium text-white">Online (30 phút qua)</h3>
            </div>
            <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center text-yellow-500"><i data-lucide="activity" class="w-5 h-5"></i></div>
        </div>
        <div class="flex items-end justify-between">
            <h2 class="text-3xl font-bold text-white"><?= $gaRealtimeData ?></h2>
            <?php if (!$gaConfigured): ?>
                <span class="text-xs text-yellow-400 bg-yellow-400/10 px-2 py-1 rounded">Cần Property ID & JSON</span>
            <?php else: ?>
                <span class="text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded">Đã Kết Nối</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- System Health Monitor -->
<h3 class="text-xl font-bold text-white mb-4 flex items-center mt-10"><i data-lucide="cpu" class="w-6 h-6 mr-2 text-purple-500"></i> Giám Sát Hệ Thống</h3>

<div class="bg-gradient-to-br from-gray-900 to-gray-800 p-8 rounded-3xl border border-gray-800 shadow-2xl relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/5 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500/5 rounded-full blur-[100px] pointer-events-none"></div>

    <?php
    $dbVersion = 'N/A';
    if ($pdo) {
        try {
            $dbVersion = $pdo->query('select version()')->fetchColumn();
        } catch(Exception $e) {}
    }
    
    $diskFree = @disk_free_space(".");
    $diskTotal = @disk_total_space(".");
    $diskText = 'Không xác định';
    $diskPercent = 0;
    if ($diskFree !== false && $diskTotal !== false && $diskTotal > 0) {
        $diskPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100);
        $diskText = round(($diskTotal - $diskFree) / 1073741824, 1) . ' GB / ' . round($diskTotal / 1073741824, 1) . ' GB';
    }
    ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative z-10">
        <!-- PHP Version -->
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-400 shrink-0 border border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.15)]">
                <i data-lucide="code-2" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-1">Phiên Bản PHP</p>
                <h4 class="text-white font-bold text-lg leading-tight">PHP <?= phpversion() ?></h4>
                <p class="text-gray-500 text-xs mt-1">Giới hạn bộ nhớ: <span class="text-gray-300"><?= ini_get('memory_limit') ?></span></p>
            </div>
        </div>

        <!-- Database -->
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-green-500/10 rounded-2xl flex items-center justify-center text-green-400 shrink-0 border border-green-500/20 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
                <i data-lucide="database" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-1">Hệ Quản Trị CSDL</p>
                <h4 class="text-white font-bold text-lg leading-tight">MySQL <?= htmlspecialchars(explode('-', $dbVersion)[0] ?? 'Unknown') ?></h4>
                <p class="text-gray-500 text-xs mt-1">Kết nối: <span class="text-gray-300 uppercase"><?= htmlspecialchars($settings['dbType'] ?? 'mysql') ?></span></p>
            </div>
        </div>

        <!-- Server OS -->
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-400 shrink-0 border border-purple-500/20 shadow-[0_0_15px_rgba(168,85,247,0.15)]">
                <i data-lucide="server" class="w-7 h-7"></i>
            </div>
            <div class="overflow-hidden w-full">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-1">Web Server</p>
                <h4 class="text-white font-bold text-lg leading-tight truncate" title="<?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?>"><?= htmlspecialchars(explode(' ', $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown')[0]) ?></h4>
                <p class="text-gray-500 text-xs mt-1">Max Upload: <span class="text-gray-300"><?= ini_get('upload_max_filesize') ?></span></p>
            </div>
        </div>

        <!-- Storage Bar -->
        <div class="lg:col-span-3 bg-gray-950/50 p-5 rounded-2xl border border-gray-800 mt-2">
            <div class="flex justify-between items-end mb-2">
                <div>
                    <h4 class="text-white font-semibold flex items-center gap-2"><i data-lucide="hard-drive" class="w-4 h-4 text-gray-400"></i> Trạng Thái Lưu Trữ (Ổ đĩa gốc)</h4>
                    <p class="text-gray-500 text-xs mt-1">Đã sử dụng <?= $diskText ?></p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black <?= $diskPercent > 80 ? 'text-red-500' : ($diskPercent > 60 ? 'text-yellow-500' : 'text-blue-500') ?>"><?= $diskPercent ?>%</span>
                </div>
            </div>
            <div class="w-full bg-gray-800 rounded-full h-2.5 mt-3 overflow-hidden">
                <div class="h-2.5 rounded-full transition-all duration-1000 <?= $diskPercent > 80 ? 'bg-red-500' : ($diskPercent > 60 ? 'bg-yellow-500' : 'bg-gradient-to-r from-blue-500 to-purple-500') ?>" style="width: <?= $diskPercent ?>%"></div>
            </div>
        </div>
    </div>
</div>
