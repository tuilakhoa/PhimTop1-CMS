<?php
class KKPhimCrawler {
    private $baseUrl = 'https://phimapi.com';
    
    private function request($endpoint) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            return json_decode($response, true);
        }
        return null;
    }

    // 1. Lấy danh sách phim mới nhất (API mới v1)
    public function getLatestMovies($page = 1) {
        return $this->request("/v1/api/danh-sach?page={$page}");
    }

    // 2. Lấy chi tiết phim theo slug (API mới v1)
    public function getMovieDetail($slug) {
        return $this->request("/v1/api/phim/" . urlencode($slug));
    }

    // 3. Lấy hình ảnh phim
    public function getMovieImages($slug) {
        return $this->request("/v1/api/phim/" . urlencode($slug) . "/images");
    }

    // 4. Lấy thông tin diễn viên / đạo diễn
    public function getMoviePeoples($slug) {
        return $this->request("/v1/api/phim/" . urlencode($slug) . "/peoples");
    }

    // 5. Lấy thông tin TMDB
    public function getTmdbInfo($type, $id) {
        // type: movie hoặc tv
        return $this->request("/tmdb/{$type}/{$id}");
    }

    // 6. Lấy thông tin IMDB
    public function getImdbInfo($id) {
        return $this->request("/imdb/title/{$id}");
    }

    // 7. Lấy danh sách thể loại
    public function getCategories() {
        return $this->request("/the-loai");
    }

    // 8. Lấy danh sách quốc gia
    public function getCountries() {
        return $this->request("/quoc-gia");
    }

    // 9. Lấy thông tin keywords
    public function getMovieKeywords($slug) {
        return $this->request("/v1/api/phim/" . urlencode($slug) . "/keywords");
    }

    // Tiện ích: Tải và lưu ảnh về local
    public function downloadImage($url, $savePath) {
        if (empty($url)) return false;
        
        $ch = curl_init($url);
        $fp = fopen($savePath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        
        return empty($error) && file_exists($savePath) && filesize($savePath) > 0;
    }
}
