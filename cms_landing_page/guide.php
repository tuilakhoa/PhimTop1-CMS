<?php
$slug = $_GET['slug'] ?? '';
$guidesDir = __DIR__ . '/guides/';
$file = $guidesDir . basename($slug) . '.html';

if (!$slug || !file_exists($file)) {
    header("Location: index.php");
    exit;
}

$content = file_get_contents($file);

// Extract title for SEO
$pageTitle = 'Hướng dẫn';
if (preg_match('/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches)) {
    $pageTitle = strip_tags($matches[1]);
}

include 'includes/header.php';
?>

<section class="section pt-32 pb-16" style="background: #070707; min-height: calc(100vh - 80px);">
    <div class="container max-w-4xl mx-auto px-4">
        <div class="mb-8">
            <a href="index.php#docs" class="text-gray-400 hover:text-white transition-colors inline-flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Quay lại danh sách
            </a>
        </div>
        
        <div class="bg-[#111111] border border-gray-800 rounded-2xl p-8 md:p-12 shadow-2xl">
            <?= $content ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
