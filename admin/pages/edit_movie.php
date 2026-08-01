<?php
$pdo = getPDO();
$slug = $_GET['slug'] ?? '';
$error = '';
$success = '';

if (!$slug) {
    echo "<div class='text-red-500 p-4'>Không tìm thấy phim.</div>";
    return;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_movie') {
    $name = $_POST['name'] ?? '';
    $origin_name = $_POST['origin_name'] ?? '';
    $thumb_url = $_POST['thumb_url'] ?? '';
    $poster_url = $_POST['poster_url'] ?? '';
    $year = (int)($_POST['year'] ?? 0);
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    $episode_current = $_POST['episode_current'] ?? '';
    $quality = $_POST['quality'] ?? '';
    $lang = $_POST['lang'] ?? '';
    $chieu_rap = isset($_POST['chieu_rap']) ? 1 : 0;
    $content = $_POST['content'] ?? '';

    if ($pdo) {
        $sql = "UPDATE movies SET 
                name = ?, origin_name = ?, thumb_url = ?, poster_url = ?, 
                year = ?, type = ?, status = ?, episode_current = ?, 
                quality = ?, lang = ?, chieu_rap = ?, content = ?
                WHERE slug = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            $name, $origin_name, $thumb_url, $poster_url, 
            $year, $type, $status, $episode_current, 
            $quality, $lang, $chieu_rap, $content, $slug
        ])) {
            $success = "Cập nhật phim thành công!";
        } else {
            $error = "Lỗi khi cập nhật vào cơ sở dữ liệu.";
        }
    }
}

// Fetch Movie Data
$movie = null;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE slug = ?");
    $stmt->execute([$slug]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$movie) {
    echo "<div class='text-red-500 p-4'>Phim không tồn tại.</div>";
    return;
}
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
        <a href="?page=movies" class="text-gray-400 hover:text-white transition-colors" title="Quay lại">
            <i data-lucide="arrow-left" class="w-6 h-6"></i>
        </a>
        Sửa Phim: <?= htmlspecialchars($movie['name']) ?>
    </h2>
    <a href="/phim/<?= $movie['slug'] ?>" target="_blank" class="bg-blue-600/20 text-blue-400 hover:bg-blue-600/30 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
        <i data-lucide="external-link" class="w-4 h-4"></i> Xem Phim
    </a>
</div>

<?php if ($success): ?>
    <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl flex items-center shadow-lg">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl flex items-center shadow-lg">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 md:p-8 shadow-xl">
    <input type="hidden" name="action" value="update_movie">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Thông tin cơ bản -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-300 border-b border-gray-800 pb-2 mb-4">Thông Tin Cơ Bản</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Tên Phim</label>
                <input type="text" name="name" value="<?= htmlspecialchars($movie['name']) ?>" required class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Tên Gốc</label>
                <input type="text" name="origin_name" value="<?= htmlspecialchars($movie['origin_name']) ?>" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Năm Phát Hành</label>
                    <input type="number" name="year" value="<?= htmlspecialchars($movie['year']) ?>" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Loại Phim</label>
                    <select name="type" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                        <option value="single" <?= $movie['type'] == 'single' ? 'selected' : '' ?>>Phim Lẻ</option>
                        <option value="series" <?= $movie['type'] == 'series' ? 'selected' : '' ?>>Phim Bộ</option>
                        <option value="hoathinh" <?= $movie['type'] == 'hoathinh' ? 'selected' : '' ?>>Hoạt Hình</option>
                        <option value="tvshows" <?= $movie['type'] == 'tvshows' ? 'selected' : '' ?>>TV Shows</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Tập Hiện Tại</label>
                    <input type="text" name="episode_current" value="<?= htmlspecialchars($movie['episode_current']) ?>" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Trạng Thái</label>
                    <select name="status" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                        <option value="ongoing" <?= $movie['status'] == 'ongoing' ? 'selected' : '' ?>>Đang chiếu</option>
                        <option value="completed" <?= $movie['status'] == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="trailer" <?= $movie['status'] == 'trailer' ? 'selected' : '' ?>>Sắp chiếu (Trailer)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Chất Lượng</label>
                    <input type="text" name="quality" value="<?= htmlspecialchars($movie['quality']) ?>" placeholder="HD, FHD, CAM..." class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Ngôn Ngữ</label>
                    <input type="text" name="lang" value="<?= htmlspecialchars($movie['lang']) ?>" placeholder="Vietsub, Thuyết minh..." class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </div>
            </div>

            <label class="flex items-center space-x-3 cursor-pointer mt-4 p-4 bg-gray-950 rounded-xl border border-gray-800">
                <input type="checkbox" name="chieu_rap" value="1" <?= $movie['chieu_rap'] ? 'checked' : '' ?> class="w-5 h-5 text-red-600 bg-gray-900 border-gray-700 rounded focus:ring-red-500 focus:ring-2">
                <span class="text-sm font-medium text-gray-300">Phim Chiếu Rạp</span>
            </label>
        </div>

        <!-- Hình ảnh & Nội dung -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-300 border-b border-gray-800 pb-2 mb-4">Hình Ảnh & Nội Dung</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Link Ảnh Thumbnail</label>
                <div class="flex gap-4 items-start">
                    <input type="url" name="thumb_url" value="<?= htmlspecialchars($movie['thumb_url']) ?>" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                    <?php if ($movie['thumb_url']): ?>
                        <img src="<?= htmlspecialchars($movie['thumb_url']) ?>" alt="Thumb" class="w-16 h-20 object-cover rounded shadow border border-gray-700">
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Link Ảnh Poster</label>
                <div class="flex gap-4 items-start">
                    <input type="url" name="poster_url" value="<?= htmlspecialchars($movie['poster_url']) ?>" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                    <?php if ($movie['poster_url']): ?>
                        <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="Poster" class="w-32 h-20 object-cover rounded shadow border border-gray-700">
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Nội Dung Phim</label>
                <textarea name="content" rows="6" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition-all custom-scrollbar"><?= htmlspecialchars($movie['content']) ?></textarea>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-end border-t border-gray-800 pt-6">
        <button type="submit" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg shadow-red-500/25 flex items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Lưu Thay Đổi
        </button>
    </div>
</form>
