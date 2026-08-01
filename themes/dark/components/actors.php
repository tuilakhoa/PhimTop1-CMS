<?php
// Fetch peoples
$peoples = [];
$ch = curl_init('https://phimapi.com/v1/api/phim/' . urlencode($slug) . '/peoples');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
$response = @curl_exec($ch);
if ($response) {
    $pData = json_decode($response, true);
    if (!empty($pData['data']['peoples'])) {
        $peoples = $pData['data']['peoples'];
    }
}
@curl_close($ch);
?>

<?php if (!empty($peoples)): ?>
<div class="mb-8">
    <h3 class="text-xl font-bold mb-3 text-white border-l-4 border-red-500 pl-2">Diễn Viên</h3>
    <div class="flex overflow-x-auto gap-4 custom-scrollbar pb-4 snap-x">
        <?php foreach ($peoples as $person): ?>
            <a href="/tim-kiem?keyword=<?= urlencode($person['name'] ?? '') ?>" class="shrink-0 w-24 md:w-28 text-center snap-start group block">
                <div class="w-20 h-20 md:w-24 md:h-24 mx-auto rounded-full overflow-hidden border-2 border-gray-700 bg-gray-800 mb-2 group-hover:border-red-500 transition-colors">
                    <?php if (!empty($person['profile_path'])): ?>
                        <img src="https://image.tmdb.org/t/p/w185<?= htmlspecialchars($person['profile_path']) ?>" alt="<?= htmlspecialchars($person['name'] ?? '') ?>" loading="lazy" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <i data-lucide="user" class="w-8 h-8 text-gray-500 group-hover:text-red-500 transition-colors"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h4 class="text-white text-xs font-medium line-clamp-1 group-hover:text-red-400 transition-colors" title="<?= htmlspecialchars($person['name'] ?? '') ?>"><?= htmlspecialchars($person['name'] ?? '') ?></h4>
                <p class="text-gray-500 text-[10px] line-clamp-1" title="<?= htmlspecialchars($person['character'] ?? '') ?>"><?= htmlspecialchars($person['character'] ?? '') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
