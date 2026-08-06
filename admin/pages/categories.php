<h2 class="text-2xl font-bold text-white mb-6">Quản Lý Thể Loại & Quốc Gia</h2>

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-4xl mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-white mb-1">Đồng Bộ Từ PhimAPI</h3>
            <p class="text-gray-400 text-sm">Tự động lấy danh sách Thể loại và Quốc gia mới nhất từ hệ thống PhimAPI.</p>
        </div>
        <button onclick="syncCategories()" id="btnSyncCategories" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center shadow-lg shadow-green-600/20">
            <i data-lucide="refresh-cw" class="w-5 h-5 mr-2"></i> Bắt Đầu Đồng Bộ
        </button>
    </div>
    <div id="syncLog" class="hidden bg-[#0c0c0c] border border-gray-800 rounded-lg p-4 font-mono text-sm text-green-400">
        <!-- Logs -->
    </div>
</div>

<?php
$repo = getCategoryRepository();
$allCats = $repo->getCategories();
$genres = [];
$countries = [];
foreach ($allCats as $row) {
    if (($row['type'] ?? '') === 'genre') $genres[] = $row;
    else if (($row['type'] ?? '') === 'country') $countries[] = $row;
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Thể Loại -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="film" class="w-5 h-5 mr-2 text-blue-500"></i> Thể Loại (<?= count($genres) ?>)
        </h3>
        <div class="flex flex-wrap gap-2 max-h-96 overflow-y-auto custom-scrollbar pr-2">
            <?php foreach ($genres as $genre): ?>
                <span class="inline-flex items-center bg-gray-800 border border-gray-700 text-gray-300 px-3 py-1.5 rounded-lg text-sm">
                    <?= htmlspecialchars($genre['name']) ?>
                </span>
            <?php endforeach; ?>
            <?php if (empty($genres)): ?>
                <p class="text-gray-500 text-sm">Chưa có dữ liệu. Hãy nhấn "Đồng Bộ" ở trên.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quốc Gia -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="globe" class="w-5 h-5 mr-2 text-purple-500"></i> Quốc Gia (<?= count($countries) ?>)
        </h3>
        <div class="flex flex-wrap gap-2 max-h-96 overflow-y-auto custom-scrollbar pr-2">
            <?php foreach ($countries as $country): ?>
                <span class="inline-flex items-center bg-gray-800 border border-gray-700 text-gray-300 px-3 py-1.5 rounded-lg text-sm">
                    <?= htmlspecialchars($country['name']) ?>
                </span>
            <?php endforeach; ?>
            <?php if (empty($countries)): ?>
                <p class="text-gray-500 text-sm">Chưa có dữ liệu. Hãy nhấn "Đồng Bộ" ở trên.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    async function syncCategories() {
        const btn = document.getElementById('btnSyncCategories');
        const log = document.getElementById('syncLog');
        
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 mr-2 animate-spin"></i> Đang đồng bộ...';
        lucide.createIcons();
        log.classList.remove('hidden');
        log.innerHTML = 'Đang tiến hành gọi API...<br>';
        
        try {
            const res = await fetch('api/sync_categories.php');
            const data = await res.json();
            
            if (data.status === 'success') {
                log.innerHTML += `✅ Đã đồng bộ thành công ${data.genres} Thể loại và ${data.countries} Quốc gia.<br>Đang tải lại trang...`;
                setTimeout(() => window.location.reload(), 1500);
            } else {
                log.innerHTML += `❌ Lỗi: ${data.message}`;
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="refresh-cw" class="w-5 h-5 mr-2"></i> Thử Lại';
                lucide.createIcons();
            }
        } catch (err) {
            log.innerHTML += `❌ Lỗi kết nối: ${err.message}`;
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="refresh-cw" class="w-5 h-5 mr-2"></i> Thử Lại';
            lucide.createIcons();
        }
    }
</script>
