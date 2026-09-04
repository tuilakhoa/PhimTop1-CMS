<?php
// Fetch peoples
$peoples = [];
if (!empty($movie['peoples_json'])) {
    $decoded = json_decode($movie['peoples_json'], true);
    if (is_array($decoded) && !empty($decoded)) {
        $peoples = $decoded;
    }
}

// Removed API fetches as per user request to use Local DB only

// Fallback to movie object actor/director
if (empty($peoples)) {
    if (!empty($movie['director'])) {
        $dirs = is_array($movie['director']) ? $movie['director'] : explode(',', $movie['director']);
        foreach ($dirs as $director) {
            $director = trim($director);
            if (!empty($director) && $director !== 'Đang cập nhật') {
                $peoples[] = [
                    'name' => $director,
                    'character' => 'Đạo diễn',
                    'profile_path' => ''
                ];
            }
        }
    }
    if (!empty($movie['actor'])) {
        $acts = is_array($movie['actor']) ? $movie['actor'] : explode(',', $movie['actor']);
        foreach ($acts as $actor) {
            $actor = trim($actor);
            if (!empty($actor) && $actor !== 'Đang cập nhật') {
                $peoples[] = [
                    'name' => $actor,
                    'character' => 'Diễn viên',
                    'profile_path' => ''
                ];
            }
        }
    }
}
?>

<?php if (!empty($peoples)): ?>
<div class="mb-10">
    <h3 class="text-xl font-bold mb-4 flex items-center text-white">
        <span class="w-1 h-5 bg-[#fcc526] mr-2 rounded"></span> Diễn viên:
    </h3>
    <div class="flex overflow-x-auto gap-4 custom-scrollbar pb-4 snap-x">
        <?php foreach ($peoples as $person): ?>
            <a href="/tim-kiem?keyword=<?= urlencode($person['name'] ?? '') ?>" class="shrink-0 w-24 md:w-28 text-center snap-start group block">
                <div class="w-20 h-20 md:w-24 md:h-24 mx-auto rounded-full overflow-hidden border-2 border-gray-700 bg-gray-800 mb-2  group-hover:border-[#fcc526]">
                    <?php 
                        if (!empty($person['profile_path'])) {
                            $imgUrl = $person['profile_path'];
                            if (!preg_match('/^http/', $imgUrl)) {
                                $imgUrl = 'https://image.tmdb.org/t/p/w185' . $imgUrl;
                            }
                    ?>
                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($person['name'] ?? '') ?>" loading="lazy" class="w-full h-full object-cover">
                    <?php } else { ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <i data-lucide="user" class="w-8 h-8 text-gray-500 group-hover:text-[#fcc526] "></i>
                        </div>
                    <?php } ?>
                </div>
                <h4 class="text-white text-xs font-medium line-clamp-1 group-hover:text-[#fcc526] " title="<?= htmlspecialchars($person['name'] ?? '') ?>"><?= htmlspecialchars($person['name'] ?? '') ?></h4>
                <p class="text-gray-500 text-[10px] line-clamp-1" title="<?= htmlspecialchars($person['character'] ?? '') ?>"><?= htmlspecialchars($person['character'] ?? '') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
