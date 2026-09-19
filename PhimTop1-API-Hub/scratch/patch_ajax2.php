<?php
$content = file_get_contents("scratch/phimtop1-crawler/includes/Ajax.php");

$save_settings = <<<EOT
        'filterCountry' => \$_POST['filterCountry'] ?? array(),
        'filterGenrePhimTop1' => isset(\$_POST['filterGenrePhimTop1']) && is_array(\$_POST['filterGenrePhimTop1']) ? array_map('sanitize_text_field', \$_POST['filterGenrePhimTop1']) : array(),
        'filterRegionPhimTop1' => isset(\$_POST['filterRegionPhimTop1']) && is_array(\$_POST['filterRegionPhimTop1']) ? array_map('sanitize_text_field', \$_POST['filterRegionPhimTop1']) : array(),
EOT;

$content = str_replace("'filterCountry' => \$_POST['filterCountry'] ?? array(),\n        'filterGenrePhimTop1' => isset(\$_POST['filterGenrePhimTop1']) && is_array(\$_POST['filterGenrePhimTop1']) ? array_map('sanitize_text_field', \$_POST['filterGenrePhimTop1']) : array(),", $save_settings, $content);
file_put_contents("scratch/phimtop1-crawler/includes/Ajax.php", $content);
