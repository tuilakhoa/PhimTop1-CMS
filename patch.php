<?php
$file = '/home/khoa/PhimTop1-CMS/plugins/kkphim-crawler/Crawler.php';
$content = file_get_contents($file);

// Replace mergeMovieData to block adult URLs
$old = <<<OLD
                            \$server['server_data'][] = [
                                'name' => \$epItem['name'] ?? '',
                                'slug' => \$epItem['slug'] ?? '',
                                'filename' => \$epItem['name'] ?? '',
                                'link_embed' => \$epItem['embed'] ?? (\$epItem['link_embed'] ?? ''),
                                'link_m3u8' => \$epItem['m3u8'] ?? (\$epItem['link_m3u8'] ?? '')
                            ];
OLD;
$new = <<<NEW
                            \$embed = \$epItem['embed'] ?? (\$epItem['link_embed'] ?? '');
                            if (strpos(\$embed, 'upload18.') !== false || strpos(\$embed, 'xhub.network') !== false || strpos(\$embed, 'topxx') !== false || strpos(\$embed, 'avdbapi') !== false) {
                                return ['movie' => null];
                            }
                            \$server['server_data'][] = [
                                'name' => \$epItem['name'] ?? '',
                                'slug' => \$epItem['slug'] ?? '',
                                'filename' => \$epItem['name'] ?? '',
                                'link_embed' => \$embed,
                                'link_m3u8' => \$epItem['m3u8'] ?? (\$epItem['link_m3u8'] ?? '')
                            ];
NEW;
$content = str_replace($old, $new, $content);

// Also block categories
$old2 = <<<OLD
        if (!empty(\$movie['category'])) {
            foreach (\$movie['category'] as \$cat) {
                if (!empty(\$cat['slug'])) \$slugsToCheck[] = \$cat['slug'];
            }
        }
OLD;
$new2 = <<<NEW
        if (!empty(\$movie['category'])) {
            foreach (\$movie['category'] as \$cat) {
                if (!empty(\$cat['slug'])) {
                    if (strpos(\$cat['slug'], 'phim-18') !== false || strpos(\$cat['slug'], 'adult') !== false) {
                        return true;
                    }
                    \$slugsToCheck[] = \$cat['slug'];
                }
            }
        }
NEW;
$content = str_replace($old2, $new2, $content);

file_put_contents($file, $content);
echo "Patched Crawler.php\n";
