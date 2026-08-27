<?php
require_once __DIR__ . '/../../includes/db.php';

// Cấu trúc mảng chứa kết quả test theo từng nhóm
$diagnostics = [
    'core' => [],
    'database' => [],
    'permissions' => [],
    'extensions' => [],
    'network' => [],
    'server' => []
];

// --- 1. CORE FILES & SETTINGS ---

// config.json Check
$dbConfigPath = __DIR__ . '/../../config.json';
if (file_exists($dbConfigPath)) {
    $content = file_get_contents($dbConfigPath);
    $decoded = json_decode($content, true);
    if ($decoded) {
        $diagnostics['core'][] = [
            'status' => 'success',
            'title' => 'File config.json',
            'message' => 'Tồn tại và đúng định dạng JSON.',
            'details' => "Loại DB: " . ($decoded['type'] ?? 'Unknown') . "\nHost: " . ($decoded['host'] ?? 'N/A')
        ];
    } else {
        $diagnostics['core'][] = [
            'status' => 'error',
            'title' => 'File config.json',
            'message' => 'Tồn tại nhưng sai định dạng JSON.',
            'details' => htmlspecialchars($content)
        ];
    }
} else {
    $diagnostics['core'][] = [
        'status' => 'error',
        'title' => 'File config.json',
        'message' => 'Không tìm thấy file config.json ở thư mục gốc.',
        'details' => ''
    ];
}

// Biến Global Settings
$settingsCache = getSettings();
if (!empty($settingsCache)) {
    global $settings_initialized;
    $diagnostics['core'][] = [
        'status' => 'success',
        'title' => 'Biến Global Settings',
        'message' => 'Hàm getSettings() trả về dữ liệu thành công.',
        'details' => "Đã khởi tạo (Initialized): " . (isset($settings_initialized) && $settings_initialized ? "TRUE" : "FALSE") . "\nSố lượng cấu hình (Keys): " . count($settingsCache)
    ];
} else {
    $diagnostics['core'][] = [
        'status' => 'error',
        'title' => 'Biến Global Settings',
        'message' => 'Hàm getSettings() trả về rỗng.',
        'details' => 'Có thể do Database hỏng hoặc cấu hình chưa được khởi tạo.'
    ];
}

// --- 2. DATABASE INTEGRITY ---
try {
    $pdo = getPDO();
    if ($pdo) {
        $diagnostics['database'][] = [
            'status' => 'success',
            'title' => 'Kết nối Database',
            'message' => 'Kết nối PDO (MySQL/MariaDB) thành công.',
            'details' => 'Client: ' . $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION)
        ];

        // Kiểm tra các bảng quan trọng
        $requiredTables = ['settings', 'movies', 'episodes', 'members'];
        $missingTables = [];
        foreach ($requiredTables as $tbl) {
            try {
                $result = $pdo->query("SELECT 1 FROM $tbl LIMIT 1");
            } catch (Exception $e) {
                $missingTables[] = $tbl;
            }
        }
        
        if (empty($missingTables)) {
            $diagnostics['database'][] = [
                'status' => 'success',
                'title' => 'Cấu trúc Bảng (Tables)',
                'message' => 'Các bảng dữ liệu cốt lõi đều tồn tại.',
                'details' => 'Đã kiểm tra: ' . implode(', ', $requiredTables)
            ];
        } else {
            $diagnostics['database'][] = [
                'status' => 'error',
                'title' => 'Cấu trúc Bảng (Tables)',
                'message' => 'Thiếu một số bảng dữ liệu quan trọng.',
                'details' => 'Các bảng bị thiếu: ' . implode(', ', $missingTables)
            ];
        }
        
        // Kiểm tra bảng Settings chi tiết
        $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $details = "Đường dẫn Admin (adminPath): " . ($row['adminPath'] ?? 'N/A') . "\n";
            $details .= "Phiên bản DB (db_version): " . ($row['db_version'] ?? 'Thiếu cột db_version!') . "\n";
            $details .= "Phiên bản CMS: " . ($row['cmsVersion'] ?? 'N/A');
            
            $diagnostics['database'][] = [
                'status' => isset($row['db_version']) ? 'success' : 'warning',
                'title' => 'Dữ liệu Bảng Settings',
                'message' => isset($row['db_version']) ? 'Bảng settings ổn định.' : 'Bảng settings thiếu cột phiên bản.',
                'details' => $details
            ];
        }
    } else {
        $config = getDbConfig();
        if ($config && isset($config['type']) && $config['type'] === 'firestore') {
            $diagnostics['database'][] = [
                'status' => 'success',
                'title' => 'Kết nối Database (Firestore)',
                'message' => 'Đang sử dụng Firestore Mode thay vì MySQL.',
                'details' => 'Project ID: ' . ($config['projectId'] ?? 'Unknown')
            ];
        } else {
            $diagnostics['database'][] = [
                'status' => 'error',
                'title' => 'Kết nối Database',
                'message' => 'Không thể kết nối được Database.',
                'details' => 'Thông tin trong config.json có thể không chính xác.'
            ];
        }
    }
} catch (Exception $e) {
    $diagnostics['database'][] = [
        'status' => 'error',
        'title' => 'Kết nối Database',
        'message' => 'Gặp lỗi (Exception) khi cố gắng kết nối DB.',
        'details' => $e->getMessage()
    ];
}

// --- 3. DIRECTORY PERMISSIONS ---
$checkDirs = [
    '/assets' => __DIR__ . '/../../assets',
    '/config.json' => __DIR__ . '/../../config.json',
    '/.htaccess' => __DIR__ . '/../../.htaccess'
];

foreach ($checkDirs as $label => $path) {
    $exists = file_exists($path);
    $writable = $exists ? is_writable($path) : false;
    
    // Nếu là thư mục assets mà chưa có thì cố tạo
    if ($label === '/assets' && !$exists) {
        @mkdir($path, 0777, true);
        $exists = file_exists($path);
        $writable = $exists ? is_writable($path) : false;
    }

    if ($exists && $writable) {
        $diagnostics['permissions'][] = [
            'status' => 'success',
            'title' => "Quyền Ghi: $label",
            'message' => "Tồn tại và có quyền ghi (Writable).",
            'details' => "Đường dẫn vật lý:\n" . realpath($path)
        ];
    } else if ($exists && !$writable) {
        $diagnostics['permissions'][] = [
            'status' => 'error',
            'title' => "Quyền Ghi: $label",
            'message' => "Tồn tại nhưng KHÔNG có quyền ghi (Permission Denied).",
            'details' => "Hãy chạy lệnh: chmod 775 hoặc chown để cấp quyền."
        ];
    } else {
        $diagnostics['permissions'][] = [
            'status' => $label === '/.htaccess' ? 'warning' : 'error',
            'title' => "Tồn Tại: $label",
            'message' => "Không tìm thấy file/thư mục này trên hệ thống.",
            'details' => $label === '/.htaccess' ? 'Sẽ gây lỗi URL Rewrite (404) nếu sử dụng Apache.' : 'Cần thiết để hệ thống hoạt động.'
        ];
    }
}

// --- 4. PHP EXTENSIONS ---
$requiredExts = [
    'pdo_mysql' => 'Kết nối CSDL MySQL',
    'curl' => 'Gửi request mạng ngoài (Kết nối API lấy dữ liệu phim)',
    'mbstring' => 'Xử lý chuỗi Unicode (Tiếng Việt)',
    'json' => 'Xử lý dữ liệu JSON',
    'gd' => 'Xử lý hình ảnh (Tạo Favicon, crop ảnh)',
    'openssl' => 'Mã hóa và HTTPS',
    'zip' => 'Nén và giải nén file'
];

$missingExts = [];
foreach ($requiredExts as $ext => $desc) {
    if (!extension_loaded($ext)) {
        $missingExts[] = "$ext ($desc)";
    }
}

if (empty($missingExts)) {
    $diagnostics['extensions'][] = [
        'status' => 'success',
        'title' => 'Các PHP Extensions',
        'message' => 'Tất cả các phần mở rộng bắt buộc đều đã cài đặt.',
        'details' => implode(', ', array_keys($requiredExts))
    ];
} else {
    $diagnostics['extensions'][] = [
        'status' => 'error',
        'title' => 'Các PHP Extensions bị thiếu',
        'message' => 'Server của bạn thiếu một vài thư viện PHP quan trọng.',
        'details' => "Hãy cài đặt:\n- " . implode("\n- ", $missingExts)
    ];
}

// --- 5. NETWORK & OUTBOUND CONNECTIVITY ---
if (extension_loaded('curl')) {
    $ch = curl_init("https://www.google.com");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 400) {
        $diagnostics['network'][] = [
            'status' => 'success',
            'title' => 'Kết Nối Mạng (Outbound)',
            'message' => 'Server có thể kết nối ra Internet thành công.',
            'details' => "HTTP Code: $httpCode\nThời gian phản hồi nhanh."
        ];
    } else {
        $diagnostics['network'][] = [
            'status' => 'error',
            'title' => 'Kết Nối Mạng (Outbound)',
            'message' => 'Server không thể kết nối ra Internet (Bị chặn Firewall).',
            'details' => "Curl Error: $error\nHTTP Code: $httpCode\nLỗi này sẽ làm chức năng cào phim không hoạt động!"
        ];
    }
} else {
    $diagnostics['network'][] = [
        'status' => 'error',
        'title' => 'Kết Nối Mạng (Outbound)',
        'message' => 'Không thể kiểm tra do thiếu cURL.',
        'details' => 'Vui lòng cài đặt cURL extension.'
    ];
}


// Server Info Details
$totalDisk = function_exists('disk_total_space') ? @disk_total_space("/") : false;
$freeDisk = function_exists('disk_free_space') ? @disk_free_space("/") : false;

function formatBytes($bytes, $precision = 2) { 
    if ($bytes === false || $bytes <= 0) return 'N/A';
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

$diskString = 'Không hỗ trợ đọc';
$diskPercent = 0;
if ($totalDisk && $freeDisk) {
    $usedDisk = $totalDisk - $freeDisk;
    $diskPercent = round(($usedDisk / $totalDisk) * 100);
    $diskString = formatBytes($usedDisk) . ' / ' . formatBytes($totalDisk) . " ($diskPercent% Đã dùng)";
}

?>

<div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center">
    <div>
        <h2 class="text-3xl font-bold text-white flex items-center">
            <i data-lucide="activity" class="w-8 h-8 mr-3 text-indigo-500"></i> Kiểm Tra Hệ Thống Chuyên Sâu
        </h2>
        <p class="text-sm text-gray-400 mt-2">Công cụ quét toàn diện các lỗ hổng về cấu hình, phân quyền, kết nối mạng và Database.</p>
    </div>
    <button onclick="window.location.reload()" class="mt-4 md:mt-0 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors flex items-center shadow-lg shadow-indigo-500/20">
        <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i> Quét Lại Máy Chủ
    </button>
</div>

<div class="space-y-8">
    <?php
    $sections = [
        'core' => ['title' => 'Lõi Hệ Thống & Cấu Hình', 'icon' => 'cpu', 'color' => 'blue'],
        'database' => ['title' => 'Cơ Sở Dữ Liệu', 'icon' => 'database', 'color' => 'emerald'],
        'permissions' => ['title' => 'Phân Quyền Thư Mục', 'icon' => 'folder-lock', 'color' => 'amber'],
        'extensions' => ['title' => 'Thư Viện PHP', 'icon' => 'puzzle', 'color' => 'purple'],
        'network' => ['title' => 'Kết Nối Mạng (Network)', 'icon' => 'globe', 'color' => 'cyan'],
    ];

    foreach ($sections as $key => $sec):
        if (empty($diagnostics[$key])) continue;
    ?>
    <div>
        <h3 class="text-lg font-bold text-white flex items-center mb-4 border-b border-gray-800 pb-2">
            <i data-lucide="<?= $sec['icon'] ?>" class="w-5 h-5 mr-2 text="<?= $sec['color'] ?>-500"></i> 
            <?= $sec['title'] ?>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
            <?php foreach ($diagnostics[$key] as $diag): ?>
                <div class="bg-gray-900 border <?= $diag['status'] === 'success' ? 'border-green-500/20' : ($diag['status'] === 'warning' ? 'border-amber-500/30' : 'border-red-500/30') ?> rounded-xl overflow-hidden shadow-lg flex flex-col transition-all hover:border-opacity-100">
                    <div class="p-4 flex items-start space-x-4 border-b border-gray-800/50">
                        <div class="p-2.5 rounded-lg <?= $diag['status'] === 'success' ? 'bg-green-500/10 text-green-500' : ($diag['status'] === 'warning' ? 'bg-amber-500/10 text-amber-500' : 'bg-red-500/10 text-red-500') ?>">
                            <i data-lucide="<?= $diag['status'] === 'success' ? 'check-circle' : ($diag['status'] === 'warning' ? 'alert-circle' : 'alert-triangle') ?>" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-200 truncate"><?= htmlspecialchars($diag['title']) ?></h4>
                            <p class="text-xs <?= $diag['status'] === 'success' ? 'text-green-400' : ($diag['status'] === 'warning' ? 'text-amber-400' : 'text-red-400') ?> mt-1 font-medium leading-relaxed">
                                <?= htmlspecialchars($diag['message']) ?>
                            </p>
                        </div>
                    </div>
                    <?php if (!empty($diag['details'])): ?>
                    <div class="p-4 bg-gray-950 flex-grow border-t border-black/20">
                        <pre class="text-xs text-gray-500 font-mono whitespace-pre-wrap break-all"><?= htmlspecialchars($diag['details']) ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="mt-12 bg-gradient-to-br from-gray-900 to-gray-950 border border-gray-800 rounded-2xl p-6 lg:p-8 shadow-2xl relative overflow-hidden">
    <!-- Decorative background element -->
    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="flex items-start space-x-5 relative z-10">
        <div class="p-4 bg-gray-800/80 text-indigo-400 rounded-2xl shadow-inner border border-gray-700/50">
            <i data-lucide="server" class="w-8 h-8"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-xl font-bold text-white mb-6">Thông Tin Máy Chủ (VPS/Hosting)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div class="bg-gray-950/50 p-4 rounded-xl border border-gray-800">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1.5 flex items-center"><i data-lucide="code-2" class="w-3.5 h-3.5 mr-1.5"></i> PHP Version</div>
                    <div class="text-lg font-mono text-gray-200"><?= phpversion() ?></div>
                </div>
                
                <div class="bg-gray-950/50 p-4 rounded-xl border border-gray-800">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1.5 flex items-center"><i data-lucide="monitor" class="w-3.5 h-3.5 mr-1.5"></i> Web Server</div>
                    <div class="text-lg font-mono text-gray-200 truncate" title="<?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?>">
                        <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?>
                    </div>
                </div>
                
                <div class="bg-gray-950/50 p-4 rounded-xl border border-gray-800">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1.5 flex items-center"><i data-lucide="memory-stick" class="w-3.5 h-3.5 mr-1.5"></i> Memory Limit</div>
                    <div class="text-lg font-mono text-gray-200"><?= ini_get('memory_limit') ?></div>
                </div>
                
                <div class="bg-gray-950/50 p-4 rounded-xl border border-gray-800">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1.5 flex items-center"><i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5"></i> Max Execution Time</div>
                    <div class="text-lg font-mono text-gray-200"><?= ini_get('max_execution_time') ?>s</div>
                </div>

                <div class="bg-gray-950/50 p-4 rounded-xl border border-gray-800">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1.5 flex items-center"><i data-lucide="upload-cloud" class="w-3.5 h-3.5 mr-1.5"></i> Max Upload Size</div>
                    <div class="text-lg font-mono text-gray-200"><?= ini_get('upload_max_filesize') ?> (Post: <?= ini_get('post_max_size') ?>)</div>
                </div>

                <div class="bg-gray-950/50 p-4 rounded-xl border border-gray-800 relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1.5 flex items-center"><i data-lucide="hard-drive" class="w-3.5 h-3.5 mr-1.5"></i> Dung Lượng Ổ Cứng</div>
                        <div class="text-sm font-mono text-gray-200 mb-2"><?= $diskString ?></div>
                        <?php if ($totalDisk): ?>
                        <div class="w-full bg-gray-800 rounded-full h-1.5 mb-1 overflow-hidden">
                            <div class="h-1.5 rounded-full <?= $diskPercent > 80 ? 'bg-red-500' : ($diskPercent > 60 ? 'bg-amber-500' : 'bg-green-500') ?>" style="width: <?= $diskPercent ?>%"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
