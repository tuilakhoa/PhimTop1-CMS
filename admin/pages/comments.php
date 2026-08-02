<?php
// Ensure admin is logged in (handled by index.php)
$pdo = getPDO();

// Ensure comments table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        movie_slug VARCHAR(255) NOT NULL,
        user_name VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'approved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // Ignore error if it already exists or on some weird DB setups
}

$repo = getCommentRepository();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $repo->deleteComment($id);
        $_SESSION['success'] = "Đã xóa bình luận thành công!";
    } elseif (isset($_POST['toggle_id'])) {
        $id = $_POST['toggle_id'];
        $current = $_POST['current_status'] ?? 'approved';
        $newStatus = $current === 'approved' ? 'pending' : 'approved';
        $repo->updateStatus($id, $newStatus);
        $_SESSION['success'] = "Đã cập nhật trạng thái bình luận!";
    }
    header("Location: ?page=comments");
    exit;
}

// Fetch Comments
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$limit = 20;

$result = $repo->getAllComments($page, $limit, '');
$totalComments = $result['total'];
$totalPages = $result['totalPages'];
$commentsList = $result['items'];

?>
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white">Quản lý Bình luận</h2>
        <p class="text-sm text-gray-400 mt-1">Quản lý và duyệt bình luận của người dùng trên hệ thống</p>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 mb-6 rounded-lg flex items-center" role="alert">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<div class="bg-gray-900 rounded-lg shadow-lg border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800 text-gray-300 text-sm border-b border-gray-700">
                    <th class="p-4 font-semibold w-16">ID</th>
                    <th class="p-4 font-semibold w-1/4">Phim (Slug)</th>
                    <th class="p-4 font-semibold w-40">Người đăng</th>
                    <th class="p-4 font-semibold">Nội dung</th>
                    <th class="p-4 font-semibold w-32">Ngày đăng</th>
                    <th class="p-4 font-semibold w-32">Trạng thái</th>
                    <th class="p-4 font-semibold w-32 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-300">
                <?php if (empty($commentsList)): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">Chưa có bình luận nào trên hệ thống.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($commentsList as $c): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                            <td class="p-4">#<?= $c['id'] ?></td>
                            <td class="p-4">
                                <a href="/phim/<?= htmlspecialchars($c['movie_slug']) ?>" target="_blank" class="text-red-500 hover:text-red-400 hover:underline">
                                    <?= htmlspecialchars($c['movie_slug']) ?>
                                </a>
                            </td>
                            <td class="p-4 font-medium text-gray-200"><?= htmlspecialchars($c['user_name']) ?></td>
                            <td class="p-4"><p class="line-clamp-2 text-gray-400"><?= htmlspecialchars($c['content']) ?></p></td>
                            <td class="p-4 text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                            <td class="p-4">
                                <?php if ($c['status'] === 'approved'): ?>
                                    <span class="px-2 py-1 bg-green-500/10 text-green-500 border border-green-500/20 rounded text-xs font-medium">Đã duyệt</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded text-xs font-medium">Đang ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="toggle_id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= htmlspecialchars($c['status']) ?>">
                                        <button type="submit" title="<?= $c['status'] === 'approved' ? 'Ẩn bình luận' : 'Hiện bình luận' ?>" class="p-1.5 rounded-md hover:bg-gray-700 text-gray-400 hover:text-white transition-colors">
                                            <?php if ($c['status'] === 'approved'): ?>
                                                <i data-lucide="eye-off" class="w-4 h-4"></i>
                                            <?php else: ?>
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này vĩnh viễn?');">
                                        <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                                        <button type="submit" title="Xóa" class="p-1.5 rounded-md hover:bg-red-500/10 text-red-500 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="p-4 border-t border-gray-800 flex items-center justify-between bg-gray-900/50">
        <span class="text-sm text-gray-500">Trang <?= $page ?> / <?= $totalPages ?></span>
        <div class="flex space-x-1">
            <?php if ($page > 1): ?>
                <a href="?page=comments&p=<?= $page - 1 ?>" class="px-3 py-1.5 border border-gray-700 rounded-md text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">Trước</a>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=comments&p=<?= $page + 1 ?>" class="px-3 py-1.5 border border-gray-700 rounded-md text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">Sau</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
