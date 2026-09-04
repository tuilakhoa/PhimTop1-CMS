<?php
function replace_in_file($file) {
    $content = file_get_contents($file);
    
    // Default to vertical (thumb_url)
    $content = preg_replace("/\!empty\(\\\$([a-zA-Z0-9_]+)\['poster_url'\]\)\s*\?\s*\\\$[a-zA-Z0-9_]+\['poster_url'\]\s*:\s*\(\\\$[a-zA-Z0-9_]+\['thumb_url'\]\s*\?\?\s*''\)/", "!empty($$1['thumb_url']) ? $$1['thumb_url'] : ($$1['poster_url'] ?? '')", $content);
    
    // Also fix the manual assignments in sidebar
    $content = str_replace(
        "empty(\$item['poster_url']) ? \$item['poster_url'] : (!empty(\$item['thumb_url']) ? \$item['thumb_url'] : '')",
        "empty(\$item['thumb_url']) ? \$item['thumb_url'] : (!empty(\$item['poster_url']) ? \$item['poster_url'] : '')",
        $content
    );

    // Banners in index.php use $featured. These should be horizontal (poster_url).
    if (strpos($file, 'index.php') !== false) {
        $content = str_replace(
            "!empty(\$featured['thumb_url']) ? \$featured['thumb_url'] : (\$featured['poster_url'] ?? '')",
            "!empty(\$featured['poster_url']) ? \$featured['poster_url'] : (\$featured['thumb_url'] ?? '')",
            $content
        );
    }

    file_put_contents($file, $content);
}

replace_in_file('themes/phimhayok/index.php');
replace_in_file('themes/phimhayok/category.php');
replace_in_file('themes/phimhayok/search.php');
replace_in_file('themes/phimhayok/movie.php');
replace_in_file('themes/phimhayok/watch.php');
replace_in_file('themes/phimhayok/header.php');
replace_in_file('themes/phimhayok/login.php');
replace_in_file('themes/phimhayok/register.php');

echo "Done\n";
