<?php
$repo = getMovieRepository();
$blockedMovies = $repo->getBlockedMoviesList();
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
        <i data-lucide="shield-alert" class="w-6 h-6 text-red-500"></i>
        Phim Đã Gỡ Bỏ (DMCA / Blocked)
    </h2>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-xl overflow-hidden">
    <?php if (empty($blockedMovies)): ?>
        <div class="p-8 text-center text-gray-500">
            <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-3 text-green-500/50"></i>
            <p>Hiện không có phim nào bị chặn.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800/50 text-gray-400 text-sm uppercase tracking-wider border-b border-gray-800">
                        <th class="px-6 py-4 font-medium">Tên Phim</th>
                        <th class="px-6 py-4 font-medium">Slug</th>
                        <th class="px-6 py-4 font-medium text-center">Ngày Chặn</th>
                        <th class="px-6 py-4 font-medium text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    <?php foreach ($blockedMovies as $movie): ?>
                        <tr class="hover:bg-gray-800/20 transition-colors" id="blocked-<?= htmlspecialchars($movie['slug']) ?>">
                            <td class="px-6 py-4">
                                <div class="text-white font-medium text-base mb-1 line-clamp-1">
                                    <?= htmlspecialchars($movie['name']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                <?= htmlspecialchars($movie['slug']) ?>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($movie['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="restoreMovie('<?= htmlspecialchars($movie['slug']) ?>', '<?= htmlspecialchars($movie['name'], ENT_QUOTES) ?>')" class="text-green-400 hover:text-green-300 hover:bg-green-400/10 transition-colors p-2 rounded-lg" title="Khôi phục phim này">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Khôi phục
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    async function restoreMovie(slug, name) {
        if (!confirm(`Bạn có chắc chắn muốn khôi phục phim "${name}"?\nPhim sẽ được cho phép hiển thị lại trên toàn bộ hệ thống.`)) {
            return;
        }
        
        try {
            const res = await fetch('api/block_movie.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'restore', slug })
            });
            
            const data = await res.json();
            if (data.status === 'success') {
                const row = document.getElementById('blocked-' + slug);
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // If no rows left, reload to show empty state
                        if (document.querySelectorAll('tbody tr').length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể khôi phục phim'));
            }
        } catch (err) {
            alert('Lỗi kết nối: ' + err.message);
        }
    }
</script>
