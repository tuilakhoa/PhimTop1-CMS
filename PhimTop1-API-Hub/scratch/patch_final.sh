#!/bin/bash
cd scratch/phimtop1-crawler

# Replace all occurrences of ophim with phimtop1 and Ophim with PhimTop1
find . -type f -exec sed -i 's/ophim/movie/g' {} +
find . -type f -exec sed -i 's/Ophim/Movie/g' {} +
find . -type f -exec sed -i 's/OPHIM/MOVIE/g' {} +

# Since we replaced ophim with movie, the plugin is now completely independent and uses "movie" post type.

# Let's fix the define.php constants since they became MOVIE_PREFIX etc.
sed -i 's/MOVIE_PREFIX/PHIMTOP1_/g' define.php
sed -i 's/crawl_movie_schedule/crawl_phimtop1_schedule/g' define.php
sed -i 's/CRAWL_PHIMTOP1_OPTION_SETTINGS/CRAWL_PHIMTOP1_OPTION_SETTINGS/g' define.php # Just in case

