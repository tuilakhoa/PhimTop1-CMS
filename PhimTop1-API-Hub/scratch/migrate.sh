#!/bin/bash
mv scratch/topxx-crawler-main scratch/phimtop1-crawler
cd scratch/phimtop1-crawler

# Rename files
mv crawl_movies_topxx.php crawl_movies_phimtop1.php
mv schedule-topxx.php schedule-phimtop1.php
mv template/backend/crawl-topxx.php template/backend/crawl-phimtop1.php
mv controllers/backend/AdminCrawlTopxx.php controllers/backend/AdminCrawlPhimtop1.php
mv public/js/script-topxx.js public/js/script-phimtop1.js

# Replace strings in all files
find . -type f -exec sed -i 's/topxx/phimtop1/g' {} +
find . -type f -exec sed -i 's/Topxx/PhimTop1/g' {} +
find . -type f -exec sed -i 's/TOPXX/PHIMTOP1/g' {} +
find . -type f -exec sed -i 's/topfilmcms/phimtop1cms/g' {} +

# Fix define.php for API URL
sed -i "s|https://phimtop1.vip|https://api.phimtop1.asia|g" define.php
sed -i "s|/api/v1|/api|g" define.php

