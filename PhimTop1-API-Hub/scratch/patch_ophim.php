<?php
$file = "scratch/phimtop1-crawler/crawl_movies_phimtop1.php";
$content = file_get_contents($file);

// Replace post type
$content = str_replace("'post_type' => 'ophim'", "'post_type' => 'movie'", $content);

// Replace taxonomies
$content = str_replace("'ophim_actors'", "'movie_actor'", $content);
$content = str_replace("'ophim_directors'", "'movie_director'", $content);
$content = str_replace("'ophim_categories'", "'movie_category'", $content);
$content = str_replace("'ophim_regions'", "'movie_country'", $content);
$content = str_replace("'ophim_years'", "'movie_year'", $content);
$content = str_replace("'ophim_genres'", "'movie_genre'", $content);

// Replace meta keys
$content = preg_replace("/'ophim_([^']+)'/", "'$1'", $content);
$content = str_replace("'_meta_id'", "'_movie_api_id'", $content);
$content = str_replace("'episode_list'", "'episodes'", $content);
$content = str_replace("\$episode_list = array_values(\$servers);", "\$episode_list = json_encode(array_values(\$servers));", $content);

file_put_contents($file, $content);

// Replace in ophim-core.php to register proper taxonomies
$core_file = "scratch/phimtop1-crawler/ophim-core.php";
$core = file_get_contents($core_file);
$core = str_replace("ophim", "movie", $core);
$core = str_replace("Ophim", "Movie", $core);
$core = str_replace("OPHIM", "MOVIE", $core);
// We don't need to register movie post type because the theme does it, but doing it again doesn't hurt or we can leave it.
file_put_contents("scratch/phimtop1-crawler/phimtop1-core.php", $core);
unlink($core_file);

// Replace requires in backend.php
$backend = file_get_contents("scratch/phimtop1-crawler/backend.php");
$backend = str_replace("ophim-core.php", "phimtop1-core.php", $backend);
file_put_contents("scratch/phimtop1-crawler/backend.php", $backend);
