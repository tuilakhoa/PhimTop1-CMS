<?php
requireAdmin();

$pluginsDir = __DIR__ . '/../../plugins';
$activePlugins = getActivePlugins();
$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'activate' || $_POST['action'] === 'deactivate') {
        $plugin = $_POST['plugin'] ?? '';
        if ($plugin && is_dir($pluginsDir . '/' . $plugin)) {
            if ($_POST['action'] === 'activate') {
                if (!in_array($plugin, $activePlugins)) {
                    $activePlugins[] = $plugin;
                    setActivePlugins($activePlugins);
                    $message = "Đã kích hoạt plugin $plugin.";
                }
            } elseif ($_POST['action'] === 'deactivate') {
                $activePlugins = array_diff($activePlugins, [$plugin]);
                setActivePlugins($activePlugins);
                $message = "Đã tắt plugin $plugin.";
            }
        }
    } elseif ($_POST['action'] === 'create_plugin') {
        $name = trim($_POST['plugin_name'] ?? '');
        $folder = trim($_POST['plugin_folder'] ?? '');
        $desc = trim($_POST['plugin_desc'] ?? '');
        $author = trim($_POST['plugin_author'] ?? '');
        
        // Basic validation for folder name (alphanumeric and dashes)
        if ($name && $folder && preg_match('/^[a-zA-Z0-9-]+$/', $folder)) {
            $newPluginPath = $pluginsDir . '/' . $folder;
            if (!is_dir($newPluginPath)) {
                if (mkdir($newPluginPath, 0777, true)) {
                    // Create plugin.json
                    $json = [
                        'name' => $name,
                        'description' => $desc,
                        'version' => '1.0.0',
                        'author' => $author
                    ];
                    file_put_contents($newPluginPath . '/plugin.json', json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    // Create basic plugin.php template
                    $phpTemplate = "<?php\n// Tên Plugin: " . htmlspecialchars($name) . "\n// Chỉnh sửa code logic của bạn tại đây.\n\nadd_action('cms_footer', function() {\n    // echo '<!-- Hello from " . htmlspecialchars($name) . " -->';\n});\n";
                    file_put_contents($newPluginPath . '/plugin.php', $phpTemplate);
                    
                    $message = "Đã tạo thành công plugin $name ($folder)!";
                } else {
                    $message = "Không thể tạo thư mục plugin. Vui lòng kiểm tra quyền ghi (CHMOD).";
                    $msgType = 'error';
                }
            } else {
                $message = "Tên thư mục '$folder' đã tồn tại. Vui lòng chọn tên khác.";
                $msgType = 'error';
            }
        } else {
            $message = "Tên thư mục không hợp lệ. Chỉ chấp nhận chữ không dấu, số và dấu gạch ngang (-).";
            $msgType = 'error';
        }
    }
}

$plugins = [];
if (is_dir($pluginsDir)) {
    $items = scandir($pluginsDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $pluginPath = $pluginsDir . '/' . $item;
        if (is_dir($pluginPath)) {
            $jsonPath = $pluginPath . '/plugin.json';
            $pluginData = [
                'folder' => $item,
                'name' => $item,
                'description' => 'Không có mô tả',
                'version' => '1.0',
                'author' => 'Unknown',
                'active' => in_array($item, $activePlugins)
            ];
            
            if (file_exists($jsonPath)) {
                $json = json_decode(file_get_contents($jsonPath), true);
                if ($json) {
                    $pluginData = array_merge($pluginData, $json);
                }
            }
            $plugins[] = $pluginData;
        }
    }
}
?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
            <i data-lucide="plug" class="w-8 h-8 text-blue-500"></i>
            Quản Lý Plugin
        </h1>
        <button onclick="document.getElementById('createPluginForm').classList.toggle('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition-colors">
            <i data-lucide="plus" class="w-5 h-5 mr-2"></i> Tạo Plugin Mới
        </button>
    </div>

    <?php if ($message): ?>
        <div class="<?= $msgType === 'error' ? 'bg-red-500/10 text-red-500 border-red-500/50' : 'bg-green-500/10 text-green-500 border-green-500/50' ?> border p-4 rounded-lg flex items-center mb-6">
            <i data-lucide="<?= $msgType === 'error' ? 'alert-circle' : 'check-circle' ?>" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Create Plugin Form (Hidden by default) -->
    <div id="createPluginForm" class="hidden bg-gray-900 rounded-xl border border-gray-800 shadow-xl overflow-hidden mb-6 p-6">
        <h2 class="text-xl font-bold text-white mb-4">Tạo Plugin Mới</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="create_plugin">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Tên Plugin</label>
                <input type="text" name="plugin_name" required placeholder="VD: Khuyến Mãi Hè" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Thư mục (a-z, 0-9, dấu gạch ngang)</label>
                <input type="text" name="plugin_folder" required pattern="[a-zA-Z0-9-]+" placeholder="VD: khuyen-mai-he" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-400 mb-1">Mô tả</label>
                <input type="text" name="plugin_desc" placeholder="Mô tả ngắn gọn về tính năng của plugin..." class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
            </div>
            <div class="md:col-span-2 flex justify-between items-center mt-2">
                <div class="w-1/2 pr-2">
                    <label class="block text-sm font-medium text-gray-400 mb-1">Tác giả</label>
                    <input type="text" name="plugin_author" placeholder="Tên của bạn" value="Admin" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
                </div>
                <div class="w-1/2 pl-2 flex justify-end items-end h-full mt-6">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center">
                        <i data-lucide="save" class="w-5 h-5 mr-2"></i> Khởi tạo
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 shadow-xl overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($plugins)): ?>
                    <div class="col-span-full p-8 bg-gray-800/50 border border-gray-700 rounded-xl">
                        <div class="text-center mb-6">
                            <i data-lucide="blocks" class="w-16 h-16 text-blue-500/50 mx-auto mb-4"></i>
                            <h3 class="text-xl font-bold text-white mb-2">Chưa có Plugin nào</h3>
                            <p class="text-gray-400 max-w-lg mx-auto">Bạn có thể tự tạo Plugin mới thông qua nút <b>"Tạo Plugin Mới"</b> ở góc trên. Hệ thống sẽ tự động sinh thư mục và file mẫu.</p>
                        </div>
                        
                        <div class="bg-gray-900 p-5 rounded-lg border border-gray-800">
                            <h4 class="text-white font-bold mb-3 flex items-center"><i data-lucide="info" class="w-5 h-5 text-blue-400 mr-2"></i> Cơ chế hoạt động của Plugin CMS:</h4>
                            <ul class="space-y-3 text-sm text-gray-400">
                                <li class="flex items-start"><i data-lucide="check" class="w-4 h-4 text-green-500 mr-2 mt-0.5 shrink-0"></i> <b>Hook Event:</b> CMS hỗ trợ các hàm <code>add_action('tên_hook', function() { ... })</code>. Các hook có sẵn bao gồm <code>cms_head</code> (nằm trong thẻ &lt;head&gt;) và <code>cms_footer</code> (trước thẻ &lt;/body&gt;).</li>
                                <li class="flex items-start"><i data-lucide="check" class="w-4 h-4 text-green-500 mr-2 mt-0.5 shrink-0"></i> <b>Tự động Load:</b> Mọi Plugin được "Kích Hoạt" sẽ được gọi file <code>plugin.php</code> tự động trên mọi trang thông qua <code>includes/db.php</code>.</li>
                                <li class="flex items-start"><i data-lucide="check" class="w-4 h-4 text-green-500 mr-2 mt-0.5 shrink-0"></i> <b>Cấu trúc chuẩn:</b> Mỗi Plugin nằm trong thư mục <code>plugins/[tên-thu-muc]</code>, cần chứa 1 file <code>plugin.json</code> để lưu thông tin (tên, tác giả) và 1 file <code>plugin.php</code> chứa logic code.</li>
                                <li class="flex items-start"><i data-lucide="check" class="w-4 h-4 text-green-500 mr-2 mt-0.5 shrink-0"></i> <b>API Tích hợp:</b> Trong Plugin, bạn có thể gọi tất cả các hàm Repository có sẵn như <code>getMovieRepository()</code>, <code>getSettings()</code>, v.v. để thao tác với dữ liệu.</li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($plugins as $plugin): ?>
                        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 flex flex-col relative overflow-hidden transition-all hover:border-gray-600">
                            <?php if ($plugin['active']): ?>
                                <div class="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase tracking-wider">Đang hoạt động</div>
                            <?php endif; ?>
                            
                            <div class="flex-grow">
                                <h3 class="text-lg font-bold text-white mb-2"><?= htmlspecialchars($plugin['name']) ?></h3>
                                <p class="text-sm text-gray-400 mb-4 line-clamp-2"><?= htmlspecialchars($plugin['description']) ?></p>
                                <div class="text-xs text-gray-500 space-y-1">
                                    <p>Phiên bản: <span class="text-gray-300"><?= htmlspecialchars($plugin['version']) ?></span></p>
                                    <p>Tác giả: <span class="text-gray-300"><?= htmlspecialchars($plugin['author']) ?></span></p>
                                    <p>Thư mục: <code class="text-blue-400"><?= htmlspecialchars($plugin['folder']) ?></code></p>
                                </div>
                            </div>
                            
                            <div class="mt-5 pt-4 border-t border-gray-700 flex justify-between items-center gap-2">
                                <form method="POST" class="flex-1">
                                    <input type="hidden" name="plugin" value="<?= htmlspecialchars($plugin['folder']) ?>">
                                    <?php if ($plugin['active']): ?>
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="w-full py-2 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg text-sm font-medium transition-colors border border-red-500/20">
                                            Tắt Plugin
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                                            Kích Hoạt
                                        </button>
                                    <?php endif; ?>
                                </form>
                                <a href="?page=plugin_editor&file=<?= urlencode('plugins/' . $plugin['folder'] . '/plugin.php') ?>" class="py-2 px-3 bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-500 rounded-lg text-sm font-medium transition-colors border border-yellow-500/20 flex items-center justify-center tooltip-trigger" title="Sửa Code">
                                    <i data-lucide="code" class="w-5 h-5"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
