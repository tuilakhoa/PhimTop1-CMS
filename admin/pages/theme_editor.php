<?php
$msg = '';
$msgType = 'success';

$themesDir = realpath(__DIR__ . '/../../themes/');
$selectedFile = $_GET['file'] ?? '';

// Handle File Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_path']) && isset($_POST['file_content'])) {
    $filePath = realpath($_POST['file_path']);
    
    // Security check: Ensure the file being edited is inside the themes directory
    if ($filePath && strpos($filePath, $themesDir) === 0) {
        $content = $_POST['file_content'];
        if (file_put_contents($filePath, $content) !== false) {
            $msg = "Đã lưu tệp thành công!";
        } else {
            $msg = "Lỗi khi lưu tệp! Hãy kiểm tra quyền (CHMOD).";
            $msgType = 'error';
        }
    } else {
        $msg = "Đường dẫn tệp không hợp lệ hoặc nằm ngoài thư mục giao diện!";
        $msgType = 'error';
    }
}

// Function to recursively scan directories
function scanThemesDir($dir) {
    $result = [];
    $cdir = scandir($dir);
    foreach ($cdir as $key => $value) {
        if (!in_array($value, array(".", ".."))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                $result[$value] = scanThemesDir($dir . DIRECTORY_SEPARATOR . $value);
            } else {
                // Only allow editing safe text files
                $ext = pathinfo($value, PATHINFO_EXTENSION);
                if (in_array($ext, ['php', 'css', 'js', 'html', 'txt', 'json'])) {
                    $result[] = $value;
                }
            }
        }
    }
    return $result;
}

$themeTree = scanThemesDir($themesDir);

// Render Tree HTML
function renderTree($tree, $basePath = 'themes') {
    $html = '<ul class="space-y-1 ml-4 border-l border-gray-700 pl-2">';
    foreach ($tree as $key => $value) {
        if (is_array($value)) {
            // It's a folder
            $html .= '<li class="my-1">';
            $html .= '<div class="flex items-center text-gray-300 font-medium cursor-pointer hover:text-white">';
            $html .= '<i data-lucide="folder" class="w-4 h-4 mr-2 text-yellow-500"></i> ' . htmlspecialchars($key);
            $html .= '</div>';
            $html .= renderTree($value, $basePath . '/' . $key);
            $html .= '</li>';
        } else {
            // It's a file
            $currentPath = $basePath . '/' . $value;
            $activeClass = (isset($_GET['file']) && $_GET['file'] === $currentPath) ? 'text-red-400 font-bold' : 'text-gray-400 hover:text-white';
            $html .= '<li class="my-1">';
            $html .= '<a href="?page=theme_editor&file=' . urlencode($currentPath) . '" class="flex items-center text-sm transition-colors ' . $activeClass . '">';
            $html .= '<i data-lucide="file-code" class="w-4 h-4 mr-2 opacity-70"></i> ' . htmlspecialchars($value);
            $html .= '</a>';
            $html .= '</li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

$fileContent = '';
$isReadable = false;
$isWritable = false;

if ($selectedFile) {
    // Basic validation to prevent arbitrary directory traversal
    $cleanPath = str_replace(['../', '..\\'], '', $selectedFile);
    $fullPath = realpath(__DIR__ . '/../../' . $cleanPath);
    
    if ($fullPath && strpos($fullPath, $themesDir) === 0 && file_exists($fullPath)) {
        $fileContent = file_get_contents($fullPath);
        $isReadable = true;
        $isWritable = is_writable($fullPath);
        
        if (!$isWritable) {
            $msg = "Tệp đang mở chỉ có quyền đọc (Read-Only). Vui lòng CHMOD 777 để lưu.";
            $msgType = 'error';
        }
    } else {
        $msg = "Không tìm thấy tệp hoặc đường dẫn không hợp lệ.";
        $msgType = 'error';
    }
}
?>
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white mb-2">Sửa Giao Diện (Theme Editor)</h2>
        <p class="text-gray-400 text-sm">Chỉnh sửa trực tiếp mã nguồn của các giao diện.</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="mb-6 <?= $msgType === 'error' ? 'bg-red-500/10 text-red-500 border-red-500/50' : 'bg-green-500/10 text-green-500 border-green-500/50' ?> border p-4 rounded-lg flex items-center">
        <i data-lucide="<?= $msgType === 'error' ? 'alert-circle' : 'check-circle' ?>" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="flex flex-col md:flex-row gap-6 h-[calc(100vh-200px)] min-h-[600px]">
    <!-- File Browser Sidebar -->
    <div class="w-full md:w-1/4 h-full flex flex-col bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div class="p-4 border-b border-gray-800 bg-gray-800/50">
            <h3 class="font-bold text-white flex items-center"><i data-lucide="folder-tree" class="w-4 h-4 mr-2 text-blue-400"></i> File Giao Diện</h3>
        </div>
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
            <div class="text-gray-200 font-bold mb-2 flex items-center">
                <i data-lucide="folder" class="w-4 h-4 mr-2 text-yellow-500"></i> themes
            </div>
            <?= renderTree($themeTree) ?>
        </div>
    </div>
    
    <!-- Code Editor Main -->
    <div class="w-full md:w-3/4 h-full flex flex-col bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <?php if ($selectedFile && $isReadable): ?>
            <div class="p-3 border-b border-gray-800 bg-gray-800/50 flex justify-between items-center">
                <div class="flex items-center text-sm font-medium text-gray-300">
                    <i data-lucide="file-edit" class="w-4 h-4 mr-2 text-gray-400"></i> 
                    <?= htmlspecialchars($selectedFile) ?>
                </div>
            </div>
            
            <form method="POST" class="flex-1 flex flex-col h-full">
                <input type="hidden" name="file_path" value="<?= htmlspecialchars($fullPath) ?>">
                
                <div class="flex-1 relative">
                    <textarea 
                        name="file_content" 
                        class="w-full h-full bg-[#1e1e1e] text-[#d4d4d4] p-4 font-mono text-[13px] leading-relaxed resize-none focus:outline-none custom-scrollbar" 
                        spellcheck="false"
                        <?= !$isWritable ? 'readonly' : '' ?>
                    ><?= htmlspecialchars($fileContent) ?></textarea>
                </div>
                
                <div class="p-4 border-t border-gray-800 bg-gray-900 flex justify-end">
                    <?php if ($isWritable): ?>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-8 rounded-lg transition-colors flex items-center">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Tệp
                    </button>
                    <?php else: ?>
                    <button type="button" class="bg-gray-700 text-gray-500 font-bold py-2.5 px-8 rounded-lg cursor-not-allowed flex items-center">
                        <i data-lucide="lock" class="w-4 h-4 mr-2"></i> Chỉ Đọc
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center h-full text-center p-12">
                <i data-lucide="file-code" class="w-16 h-16 text-gray-700 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400 mb-2">Trình Sửa Giao Diện</h3>
                <p class="text-gray-500 max-w-sm">Chọn một tệp từ cây thư mục bên trái để bắt đầu chỉnh sửa. Hỗ trợ chỉnh sửa PHP, CSS, JS.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
