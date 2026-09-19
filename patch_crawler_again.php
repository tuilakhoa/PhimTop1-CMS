<?php
$file = '/home/khoa/PhimTop1-CMS/plugins/kkphim-crawler/Crawler.php';
$content = file_get_contents($file);

$old = <<<OLD
        return [
            'movie' => \$mainMovie,
OLD;
$new = <<<NEW
        if (!empty(\$mainMovie['thumb_url']) && (strpos(\$mainMovie['thumb_url'], 'upload18') !== false || strpos(\$mainMovie['thumb_url'], 'topxx') !== false || strpos(\$mainMovie['thumb_url'], 'avdbapi') !== false)) {
            return ['movie' => null];
        }
        if (!empty(\$mainMovie['poster_url']) && (strpos(\$mainMovie['poster_url'], 'upload18') !== false || strpos(\$mainMovie['poster_url'], 'topxx') !== false || strpos(\$mainMovie['poster_url'], 'avdbapi') !== false)) {
            return ['movie' => null];
        }
        return [
            'movie' => \$mainMovie,
NEW;
$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo "Patched Crawler.php again\n";
