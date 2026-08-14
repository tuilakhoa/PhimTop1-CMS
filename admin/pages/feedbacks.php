<h2 class="text-2xl font-bold text-white mb-6">Quản Lý Phản Hồi Ý Kiến</h2>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-lg mb-8">
    <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-800/50">
        <h3 class="text-lg font-semibold text-white">Danh Sách Phản Hồi</h3>
    </div>
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800/80 border-b border-gray-700 text-sm">
                    <th class="p-4 font-medium text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="p-4 font-medium text-gray-400 uppercase tracking-wider">Email User</th>
                    <th class="p-4 font-medium text-gray-400 uppercase tracking-wider">Nội Dung</th>
                    <th class="p-4 font-medium text-gray-400 uppercase tracking-wider w-32">Trạng Thái</th>
                    <th class="p-4 font-medium text-gray-400 uppercase tracking-wider text-right">Ngày Gửi</th>
                    <th class="p-4 font-medium text-gray-400 uppercase tracking-wider text-right w-24">Hành Động</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-800/50">
                <?php
                $pdo = getPDO();
                if ($pdo) {
                    $stmt = $pdo->query("SELECT * FROM user_feedbacks ORDER BY created_at DESC");
                    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($feedbacks) === 0): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">Chưa có phản hồi nào.</td>
                        </tr>
                    <?php else: 
                        foreach ($feedbacks as $fb): ?>
                            <tr class="hover:bg-gray-800/30 transition-colors">
                                <td class="p-4 text-gray-400">#<?= $fb['id'] ?></td>
                                <td class="p-4 text-gray-300 font-medium"><?= htmlspecialchars($fb['user_email']) ?></td>
                                <td class="p-4 text-gray-300 max-w-md truncate" title="<?= htmlspecialchars($fb['message']) ?>"><?= htmlspecialchars($fb['message']) ?></td>
                                <td class="p-4">
                                    <?php if ($fb['status'] === 'resolved'): ?>
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-500/10 text-green-400 border border-green-500/20">Đã Xử Lý</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Đang Chờ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-gray-500 text-right"><?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?></td>
                                <td class="p-4 text-right">
                                    <?php if ($fb['status'] !== 'resolved'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="resolve_feedback">
                                            <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                                            <button type="submit" class="text-green-500 hover:text-green-400 p-1.5 rounded hover:bg-gray-800 transition-colors tooltip" title="Đánh dấu đã xử lý">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa phản hồi này?');">
                                        <input type="hidden" name="action" value="delete_feedback">
                                        <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-400 p-1.5 rounded hover:bg-gray-800 transition-colors tooltip" title="Xóa">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach;
                    endif;
                } else {
                    echo '<tr><td colspan="6" class="p-8 text-center text-red-500">Lỗi kết nối cơ sở dữ liệu.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
