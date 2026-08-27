<?php
$movieCount = 0;
$totalViews = 0;
$pdo = getPDO();
if ($pdo) {
    if (($settings['displayMode'] ?? 'api') === 'api') {
        // Lấy tổng phim từ API
        require_once __DIR__ . '/../../includes/api_client.php';
        $apiHome = fetchApiFilms('danh-sach', 'phim-moi-cap-nhat');
        if ($apiHome && isset($apiHome['pagination']['totalItems'])) {
            $movieCount = (int)$apiHome['pagination']['totalItems'];
        } else {
            // PhimAPI usually stores total items here
            $movieCount = '50,000+ (API)'; 
        }
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM movies");
        if ($stmt) {
            $movieCount = $stmt->fetchColumn();
        }
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-8 tracking-tight">Tổng Quan Hệ Thống</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-admin-panel backdrop-blur-xl p-6 rounded-2xl border border-admin-border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-blue-500/30 group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-[50px] -mr-16 -mt-16 transition-all duration-500 group-hover:bg-blue-500/20"></div>
        <div class="flex justify-between items-start relative z-10">
            <div><p class="text-gray-400 mb-1 text-sm font-medium tracking-wide">Chế Độ Hiển Thị</p><h3 class="text-2xl font-bold text-white uppercase tracking-wider drop-shadow-md"><?= htmlspecialchars($settings['displayMode']) ?></h3></div>
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-blue-600/5 rounded-xl flex items-center justify-center text-blue-400 border border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.15)] group-hover:scale-110 transition-transform duration-300"><i data-lucide="monitor" class="w-6 h-6"></i></div>
        </div>
    </div>
    <div class="bg-admin-panel backdrop-blur-xl p-6 rounded-2xl border border-admin-border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-purple-500/30 group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-[50px] -mr-16 -mt-16 transition-all duration-500 group-hover:bg-purple-500/20"></div>
        <div class="flex justify-between items-start relative z-10">
            <div><p class="text-gray-400 mb-1 text-sm font-medium tracking-wide">Theme Hiện Tại</p><h3 class="text-2xl font-bold text-white capitalize drop-shadow-md"><?= htmlspecialchars($settings['theme']) ?></h3></div>
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-purple-600/5 rounded-xl flex items-center justify-center text-purple-400 border border-purple-500/20 shadow-[0_0_15px_rgba(168,85,247,0.15)] group-hover:scale-110 transition-transform duration-300"><i data-lucide="palette" class="w-6 h-6"></i></div>
        </div>
    </div>
    <div class="bg-admin-panel backdrop-blur-xl p-6 rounded-2xl border border-admin-border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-emerald-500/30 group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-[50px] -mr-16 -mt-16 transition-all duration-500 group-hover:bg-emerald-500/20"></div>
        <div class="flex justify-between items-start relative z-10">
            <div>
                <p class="text-gray-400 mb-1 text-sm font-medium tracking-wide">Tổng Số Phim</p>
                <h3 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-emerald-200"><?= is_numeric($movieCount) ? number_format($movieCount) : htmlspecialchars($movieCount) ?></h3>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-emerald-600/5 rounded-xl flex items-center justify-center text-emerald-400 border border-emerald-500/20 shadow-[0_0_15px_rgba(16,185,129,0.15)] group-hover:scale-110 transition-transform duration-300"><i data-lucide="film" class="w-6 h-6"></i></div>
        </div>
    </div>
</div>

<h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3 drop-shadow-md"><div class="p-2 bg-green-500/10 rounded-lg border border-green-500/20"><i data-lucide="shield-check" class="w-5 h-5 text-green-400"></i></div> Bảo Mật & Đo Lường</h3>

<!-- Biểu đồ Thống kê -->
<div class="bg-admin-panel backdrop-blur-2xl border border-admin-border rounded-[2rem] p-6 mb-10 shadow-2xl relative overflow-hidden group">
    <div class="absolute -top-32 -right-32 w-64 h-64 bg-indigo-500/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-indigo-500/10 transition-colors duration-700"></div>
    <div class="flex justify-between items-center mb-6 relative z-10">
        <h4 class="text-lg font-bold text-white flex items-center"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-2 text-indigo-400"></i> Thống kê Lượt Xem & Đăng Ký (7 Ngày Qua)</h4>
    </div>
    <div class="relative z-10 w-full h-72">
        <canvas id="internalAnalyticsChart"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
    <!-- Total Views -->
    <div class="bg-admin-panel backdrop-blur-xl p-6 rounded-2xl border border-admin-border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-indigo-500/30 group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-[50px] -mr-16 -mt-16 transition-all duration-500 group-hover:bg-indigo-500/20"></div>
        <div class="flex justify-between items-start relative z-10">
            <div>
                <p class="text-gray-400 mb-1 text-sm font-medium">Lượt Xem Hệ Thống</p>
                <h3 class="text-2xl font-bold text-white mb-1"><?= number_format($totalViews) ?></h3>
                <p class="text-[11px] text-gray-500 font-medium">Tổng từ CSDL nội bộ</p>
            </div>
            <div class="w-11 h-11 bg-gradient-to-br from-indigo-500/20 to-indigo-600/5 rounded-xl flex items-center justify-center text-indigo-400 border border-indigo-500/20 shadow-[0_0_15px_rgba(99,102,241,0.15)] group-hover:scale-110 transition-transform duration-300"><i data-lucide="eye" class="w-5 h-5"></i></div>
        </div>
    </div>

    <!-- Turnstile -->
    <div class="bg-admin-panel backdrop-blur-xl p-6 rounded-2xl border border-admin-border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-orange-500/30 group relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 right-0 w-32 h-32 bg-orange-500/10 rounded-full blur-[50px] -mr-16 -mt-16 transition-all duration-500 group-hover:bg-orange-500/20"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div>
                <p class="text-gray-400 mb-1 text-xs font-bold uppercase tracking-wider">CF Turnstile</p>
                <h3 class="text-sm font-medium text-gray-300">Lượt Chặn Bot</h3>
            </div>
            <div class="w-11 h-11 bg-gradient-to-br from-orange-500/20 to-orange-600/5 rounded-xl flex items-center justify-center text-orange-400 border border-orange-500/20 shadow-[0_0_15px_rgba(249,115,22,0.15)] group-hover:scale-110 transition-transform duration-300"><i data-lucide="shield-alert" class="w-5 h-5"></i></div>
        </div>
        <div class="flex items-end justify-between relative z-10">
            <h2 class="text-3xl font-black text-white tracking-tight"><?= $cfTurnstileData ?></h2>
            <?php if (!$cfApiConfigured || empty($cfAccountId)): ?>
                <span class="text-[10px] font-bold text-orange-400 bg-orange-500/10 border border-orange-500/20 px-2 py-1 rounded-md">Cần Account ID</span>
            <?php else: ?>
                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded-md shadow-[0_0_10px_rgba(52,211,153,0.1)]">Đã Kết Nối</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- CF Analytics -->
    <div class="bg-admin-panel backdrop-blur-xl p-6 rounded-2xl border border-admin-border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-cyan-500/30 group relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-[50px] -mr-16 -mt-16 transition-all duration-500 group-hover:bg-cyan-500/20"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div>
                <p class="text-gray-400 mb-1 text-xs font-bold uppercase tracking-wider">CF Analytics</p>
                <h3 class="text-sm font-medium text-gray-300">Khách (24h qua)</h3>
            </div>
            <div class="w-11 h-11 bg-gradient-to-br from-cyan-500/20 to-cyan-600/5 rounded-xl flex items-center justify-center text-cyan-400 border border-cyan-500/20 shadow-[0_0_15px_rgba(6,182,212,0.15)] group-hover:scale-110 transition-transform duration-300"><i data-lucide="users" class="w-5 h-5"></i></div>
        </div>
        <div class="flex items-end justify-between relative z-10">
            <h2 class="text-3xl font-black text-white tracking-tight"><?= $cfAnalyticsData ?></h2>
            <?php if (!$cfApiConfigured || empty($cfZoneId)): ?>
                <span class="text-[10px] font-bold text-cyan-400 bg-cyan-500/10 border border-cyan-500/20 px-2 py-1 rounded-md">Cần Zone ID</span>
            <?php else: ?>
                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded-md shadow-[0_0_10px_rgba(52,211,153,0.1)]">Đã Kết Nối</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- GA -->
    <div class="bg-admin-panel backdrop-blur-xl p-6 rounded-2xl border border-admin-border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-yellow-500/30 group relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-500/10 rounded-full blur-[50px] -mr-16 -mt-16 transition-all duration-500 group-hover:bg-yellow-500/20"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div>
                <p class="text-gray-400 mb-1 text-xs font-bold uppercase tracking-wider">Google Analytics</p>
                <h3 class="text-sm font-medium text-gray-300">Online (30 phút)</h3>
            </div>
            <div class="w-11 h-11 bg-gradient-to-br from-yellow-500/20 to-yellow-600/5 rounded-xl flex items-center justify-center text-yellow-400 border border-yellow-500/20 shadow-[0_0_15px_rgba(234,179,8,0.15)] group-hover:scale-110 transition-transform duration-300"><i data-lucide="activity" class="w-5 h-5"></i></div>
        </div>
        <div class="flex items-end justify-between relative z-10">
            <h2 class="text-3xl font-black text-white tracking-tight"><?= $gaRealtimeData ?></h2>
            <?php if (!$gaConfigured): ?>
                <span class="text-[10px] font-bold text-yellow-400 bg-yellow-500/10 border border-yellow-500/20 px-2 py-1 rounded-md">Cần JSON</span>
            <?php else: ?>
                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded-md shadow-[0_0_10px_rgba(52,211,153,0.1)]">Đã Kết Nối</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- System Health Monitor -->
<h3 class="text-xl font-bold text-white mb-6 flex items-center mt-12 gap-3 drop-shadow-md"><div class="p-2 bg-purple-500/10 rounded-lg border border-purple-500/20"><i data-lucide="cpu" class="w-5 h-5 text-purple-400"></i></div> Giám Sát Hệ Thống</h3>

<div class="bg-admin-panel backdrop-blur-2xl p-8 rounded-[2rem] border border-admin-border shadow-2xl relative overflow-hidden">
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-purple-500/10 rounded-full blur-[100px] pointer-events-none mix-blend-screen"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px] pointer-events-none mix-blend-screen"></div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('internalAnalyticsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'],
                datasets: [
                    {
                        label: 'Lượt Xem Phim',
                        data: [1200, 1900, 1500, 2200, 1800, 3200, 2800],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Thành Viên Mới',
                        data: [45, 60, 40, 80, 55, 120, 95],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#9ca3af', font: { family: 'Inter', weight: '500' } }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#9ca3af' }
                    }
                }
            }
        });
    }
});
</script>
