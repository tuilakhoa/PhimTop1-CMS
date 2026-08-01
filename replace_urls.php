<?php
$dir = __DIR__ . '/themes';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$replacements = array(
    'href="/phim/' => 'href="/<?= $settings["slugMovie"] ?? "phim" ?>/',
    'href="/xem-phim/' => 'href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/',
    'href="/danh-sach/' => 'href="/<?= $settings["slugList"] ?? "danh-sach" ?>/',
    'href="/the-loai/' => 'href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/',
    'href="/quoc-gia/' => 'href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/'
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;
        
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        $replaceStr = 'href="/<?= $settings["slugGenre"] ?? "the-loai" ?>/$1"';
        $content = preg_replace('/href="category\.php\?slug=([^"&]+)"/', $replaceStr, $content);
        
        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated: " . $path . "\n";
        }
    }
}
echo "Done.\n";
