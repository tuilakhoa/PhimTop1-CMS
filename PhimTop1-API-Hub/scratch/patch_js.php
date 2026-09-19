<?php
$js = file_get_contents("scratch/phimtop1-crawler/public/js/script-phimtop1.js");

// Add filter_region_phimtop1 logic inside "Bỏ qua thể loại PhimTop1" onChange to save instantly
$change = <<<EOT
    $("input[name='filter_genre_phimtop1[]'], input[name='filter_region_phimtop1[]']").on("change", function () {
        var arrG = [];
        var arrR = [];
        $("input[name='filter_genre_phimtop1[]']:checked").each(function () { arrG.push($(this).val()); });
        $("input[name='filter_region_phimtop1[]']:checked").each(function () { arrR.push($(this).val()); });
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "crawl_phimtop1_save_filter_genre",
                filterGenrePhimTop1: arrG,
                filterRegionPhimTop1: arrR,
EOT;
$js = preg_replace('/\$\("input\[name=\'filter_genre_phimtop1\[\]\'\]"\)\.on\("change", function \(\) \{.*?\n.*?filterGenrePhimTop1: arr,/s', $change, $js);

// Add inputFilterRegionPhimTop1 to crawl_movies array
$js = str_replace("inputFilterGenrePhimTop1 = [];\n        \$(\"input[name='filter_genre_phimtop1[]']:checked\").each(function () {\n            inputFilterGenrePhimTop1.push(\$(this).val());\n        });", "inputFilterGenrePhimTop1 = [];\n        \$(\"input[name='filter_genre_phimtop1[]']:checked\").each(function () {\n            inputFilterGenrePhimTop1.push(\$(this).val());\n        });\n        inputFilterRegionPhimTop1 = [];\n        \$(\"input[name='filter_region_phimtop1[]']:checked\").each(function () {\n            inputFilterRegionPhimTop1.push(\$(this).val());\n        });", $js);

$js = str_replace("filterGenre: inputFilterGenrePhimTop1,", "filterGenre: inputFilterGenrePhimTop1,\n                filterRegion: inputFilterRegionPhimTop1,", $js);

// Add filterRegionPhimTop1 to Save config
$js = str_replace("let filterGenrePhimTop1 = [];\n        \$(\"input[name='filter_genre_phimtop1[]']:checked\").each(function () {\n            filterGenrePhimTop1.push(\$(this).val());\n        });", "let filterGenrePhimTop1 = [];\n        \$(\"input[name='filter_genre_phimtop1[]']:checked\").each(function () {\n            filterGenrePhimTop1.push(\$(this).val());\n        });\n\n        let filterRegionPhimTop1 = [];\n        \$(\"input[name='filter_region_phimtop1[]']:checked\").each(function () {\n            filterRegionPhimTop1.push(\$(this).val());\n        });", $js);

$js = str_replace("filterGenrePhimTop1,\n                crawl_resize_size_thumb,", "filterGenrePhimTop1,\n                filterRegionPhimTop1,\n                crawl_resize_size_thumb,", $js);

file_put_contents("scratch/phimtop1-crawler/public/js/script-phimtop1.js", $js);
