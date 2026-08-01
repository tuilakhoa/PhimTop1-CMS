<?php
$pdo = getPDO();
if (!$pdo) {
    echo "<div class='p-6 bg-red-900/50 text-red-400 rounded-xl'>Không thể kết nối cơ sở dữ liệu MySQL. Tính năng này không hỗ trợ Firestore.</div>";
    return;
}

$msg = '';
$msgType = 'success';
$table = $_GET['table'] ?? '';

// Get all tables
$tables = [];
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $msg = "Lỗi khi lấy danh sách bảng: " . $e->getMessage();
    $msgType = 'error';
}

if ($table && !in_array($table, $tables)) {
    $table = '';
}

$columns = [];
$pk = '';
if ($table) {
    try {
        $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        $pkRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $pk = $pkRow ? $pkRow['Column_name'] : '';
        
        $stmt = $pdo->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $msg = "Lỗi khi đọc cấu trúc bảng: " . $e->getMessage();
        $msgType = 'error';
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $table) {
    $action = $_POST['db_action'] ?? '';
    
    try {
        if ($action === 'delete' && $pk) {
            $id = $_POST['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
            $stmt->execute([$id]);
            $msg = "Đã xóa bản ghi thành công!";
        } 
        elseif ($action === 'insert' || $action === 'update') {
            $data = [];
            foreach ($columns as $col) {
                $colName = $col['Field'];
                // Bỏ qua cột tự động tăng nếu là insert
                if ($action === 'insert' && strpos($col['Extra'], 'auto_increment') !== false) {
                    continue;
                }
                // Nếu update, bỏ qua PK unless we want to change it (usually we don't)
                if ($action === 'update' && $colName === $pk) {
                    continue;
                }
                if (isset($_POST[$colName])) {
                    $data[$colName] = $_POST[$colName];
                }
            }
            
            if ($action === 'insert') {
                $fields = array_keys($data);
                $placeholders = array_fill(0, count($fields), '?');
                $sql = "INSERT INTO `$table` (`" . implode("`,`", $fields) . "`) VALUES (" . implode(",", $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($data));
                $msg = "Đã thêm bản ghi mới thành công!";
            } else { // update
                $id = $_POST['id'] ?? '';
                if ($id && $pk) {
                    $setClauses = [];
                    $values = [];
                    foreach ($data as $k => $v) {
                        $setClauses[] = "`$k` = ?";
                        $values[] = $v;
                    }
                    $values[] = $id; // For WHERE
                    $sql = "UPDATE `$table` SET " . implode(", ", $setClauses) . " WHERE `$pk` = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($values);
                    $msg = "Đã cập nhật bản ghi thành công!";
                }
            }
        }
    } catch (Exception $e) {
        $msg = "Lỗi Database: " . $e->getMessage();
        $msgType = 'error';
    }
}

// Display Modes
$mode = $_GET['mode'] ?? 'list';
$editId = $_GET['id'] ?? '';

?>
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white mb-2">Quản Lý Database</h2>
        <p class="text-gray-400 text-sm">Chỉnh sửa trực tiếp dữ liệu các bảng trong hệ thống.</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="mb-6 <?= $msgType === 'error' ? 'bg-red-500/10 text-red-500 border-red-500/50' : 'bg-green-500/10 text-green-500 border-green-500/50' ?> border p-4 rounded-lg flex items-center">
        <i data-lucide="<?= $msgType === 'error' ? 'alert-circle' : 'check-circle' ?>" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Sidebar: Table List -->
    <div class="w-full md:w-1/4">
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-800 bg-gray-800/50">
                <h3 class="font-bold text-white flex items-center"><i data-lucide="database" class="w-4 h-4 mr-2 text-red-500"></i> Danh Sách Bảng</h3>
            </div>
            <div class="max-h-[600px] overflow-y-auto custom-scrollbar p-2">
                <?php foreach ($tables as $t): ?>
                    <a href="?page=database&table=<?= $t ?>" class="block px-4 py-2 rounded-lg text-sm transition-colors <?= $table === $t ? 'bg-red-600 text-white font-medium' : 'text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                        <?= $t ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="w-full md:w-3/4">
        <?php if ($table): ?>
            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                <div class="p-4 border-b border-gray-800 bg-gray-800/50 flex justify-between items-center">
                    <h3 class="font-bold text-white text-lg">Bảng: <?= htmlspecialchars($table) ?></h3>
                    <?php if ($mode === 'list'): ?>
                    <a href="?page=database&table=<?= $table ?>&mode=insert" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center font-medium transition-colors">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Thêm Bản Ghi
                    </a>
                    <?php else: ?>
                    <a href="?page=database&table=<?= $table ?>" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm flex items-center font-medium transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Quay lại
                    </a>
                    <?php endif; ?>
                </div>
                
                <div class="p-4">
                    <?php if ($mode === 'list'): 
                        $page = max(1, (int)($_GET['p'] ?? 1));
                        $limit = 20;
                        $offset = ($page - 1) * $limit;
                        
                        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                        $totalRows = $countStmt->fetchColumn();
                        $totalPages = ceil($totalRows / $limit);
                        
                        $stmt = $pdo->query("SELECT * FROM `$table` LIMIT $limit OFFSET $offset");
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                        <?php if (empty($rows)): ?>
                            <div class="text-center py-8 text-gray-500">Bảng này chưa có dữ liệu.</div>
                        <?php else: ?>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 bg-gray-800/50 text-gray-400 font-medium text-sm whitespace-nowrap">Thao tác</th>
                                            <?php foreach ($columns as $col): ?>
                                                <th class="px-4 py-3 bg-gray-800/50 text-gray-400 font-medium text-sm whitespace-nowrap" title="<?= htmlspecialchars($col['Type']) ?>">
                                                    <?= htmlspecialchars($col['Field']) ?>
                                                    <?php if($col['Field'] === $pk): ?> <i data-lucide="key" class="w-3 h-3 inline text-yellow-500"></i> <?php endif; ?>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        <?php foreach ($rows as $row): ?>
                                            <tr class="hover:bg-gray-800/30 transition-colors">
                                                <td class="px-4 py-3 whitespace-nowrap flex gap-2">
                                                    <?php if ($pk && isset($row[$pk])): ?>
                                                        <a href="?page=database&table=<?= $table ?>&mode=edit&id=<?= urlencode($row[$pk]) ?>" class="text-blue-500 hover:text-blue-400 p-1 bg-blue-500/10 rounded" title="Sửa">
                                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                        </a>
                                                        <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi này?');" class="inline">
                                                            <input type="hidden" name="db_action" value="delete">
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars($row[$pk]) ?>">
                                                            <button type="submit" class="text-red-500 hover:text-red-400 p-1 bg-red-500/10 rounded" title="Xóa">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-xs text-gray-600">No PK</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php foreach ($columns as $col): 
                                                    $val = $row[$col['Field']];
                                                    $displayVal = $val;
                                                    if (strlen($val) > 50) $displayVal = substr($val, 0, 50) . '...';
                                                ?>
                                                    <td class="px-4 py-3 text-sm text-gray-300 max-w-[200px] truncate" title="<?= htmlspecialchars($val) ?>">
                                                        <?= htmlspecialchars($displayVal) ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <div class="mt-6 flex justify-between items-center text-sm text-gray-400">
                                <div>Hiển thị <?= count($rows) ?> / <?= $totalRows ?> bản ghi</div>
                                <div class="flex gap-1">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=database&table=<?= $table ?>&p=<?= $page - 1 ?>" class="px-3 py-1 bg-gray-800 rounded hover:bg-gray-700">Trang trước</a>
                                    <?php endif; ?>
                                    
                                    <span class="px-3 py-1 text-white">Trang <?= $page ?> / <?= $totalPages ?></span>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=database&table=<?= $table ?>&p=<?= $page + 1 ?>" class="px-3 py-1 bg-gray-800 rounded hover:bg-gray-700">Trang sau</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                        
                    <?php elseif ($mode === 'insert' || $mode === 'edit'): 
                        $editData = [];
                        if ($mode === 'edit' && $pk && $editId) {
                            $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
                            $stmt->execute([$editId]);
                            $editData = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    ?>
                        <form method="POST" class="space-y-4 max-w-4xl">
                            <input type="hidden" name="db_action" value="<?= $mode ?>">
                            <?php if ($mode === 'edit'): ?>
                                <input type="hidden" name="id" value="<?= htmlspecialchars($editId) ?>">
                            <?php endif; ?>
                            
                            <?php foreach ($columns as $col): 
                                $colName = $col['Field'];
                                $isAutoInc = strpos($col['Extra'], 'auto_increment') !== false;
                                if ($mode === 'insert' && $isAutoInc) continue; // Skip AI on insert
                                
                                $val = $editData[$colName] ?? $col['Default'] ?? '';
                                $type = $col['Type'];
                                $isTextarea = strpos($type, 'text') !== false;
                            ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">
                                        <?= htmlspecialchars($colName) ?> 
                                        <span class="text-xs text-gray-600 ml-2">(<?= htmlspecialchars($type) ?>)</span>
                                    </label>
                                    <?php if ($mode === 'edit' && $colName === $pk): ?>
                                        <input type="text" value="<?= htmlspecialchars($val) ?>" disabled class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-gray-500 cursor-not-allowed">
                                    <?php elseif ($isTextarea): ?>
                                        <textarea name="<?= $colName ?>" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500"><?= htmlspecialchars($val) ?></textarea>
                                    <?php else: ?>
                                        <input type="text" name="<?= $colName ?>" value="<?= htmlspecialchars($val) ?>" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="pt-4 border-t border-gray-800">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center">
                                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Bản Ghi
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-12 text-center flex flex-col items-center justify-center h-full min-h-[400px]">
                <i data-lucide="database" class="w-16 h-16 text-gray-700 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400 mb-2">Chọn một bảng</h3>
                <p class="text-gray-500 max-w-sm">Vui lòng chọn một bảng từ danh sách bên trái để xem và chỉnh sửa dữ liệu.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
