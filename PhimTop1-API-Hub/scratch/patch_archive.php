<?php
$content = file_get_contents('public/downloads/phimtop1-theme/archive-movie.php');
$content = preg_replace('/\$paged.*new WP_Query\(\$args\);/s', '', $content);
$content = str_replace('if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();', 'if (have_posts()) : while (have_posts()) : the_post();', $content);
$content = str_replace('<?php endwhile; wp_reset_postdata(); else: ?>', '<?php endwhile; else: ?>', $content);
$content = str_replace('\'total\' => $query->max_num_pages,', '/* total uses global wp_query */', $content);
file_put_contents('public/downloads/phimtop1-theme/archive-movie.php', $content);
