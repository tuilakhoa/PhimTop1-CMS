<?php

class OFim_AdminCrawlPhimTop1_Controller{
    private $cache;
    public function __construct(){
        $this->cache = new oCache();
    }

    public function display(){
        $schedule_log = $this->getLastLog();
        $genres = $this->getPhimTop1Genres();
        $regions = $this->getPhimTop1Regions();
        include_once(OFIM_TEMPLADE_PATCH."/backend/crawl-phimtop1.php"); // included template file
    }

    /**
     * Lấy danh sách thể loại từ API PhimTop1 để làm bộ lọc
     */
            public function getPhimTop1Genres() {
        return $this->cache->remember('phimtop1_genres.txt', 3600, function() {
            $url = API_DOMAIN . '/the-loai';
            $resp = @file_get_contents($url);
            if ($resp === false) {
                return array();
            }
            $json = json_decode($resp);
            if (empty($json->data) || empty($json->data->items)) {
                return array();
            }
            $genres = array();
            foreach ($json->data->items as $item) {
                $genres[] = array(
                    'code' => $item->name,
                    'name_vi' => $item->name,
                    'name_en' => $item->name,
                );
            }
            return $genres;
        });
    }

        public function getPhimTop1Regions() {
        return $this->cache->remember('phimtop1_regions.txt', 3600, function() {
            $url = API_DOMAIN . '/quoc-gia';
            $resp = @file_get_contents($url);
            if ($resp === false) return array();
            $json = json_decode($resp);
            if (empty($json->data) || empty($json->data->items)) return array();
            $regions = array();
            foreach ($json->data->items as $item) {
                $regions[] = array(
                    'code' => $item->name,
                    'name_vi' => $item->name,
                    'name_en' => $item->name,
                );
            }
            return $regions;
        });
    }

    public function getLastLog() {
        $log_path = WP_CONTENT_DIR  . '/crawl_phimtop1_logs';
        $log_filename = 'log_' . date('d-m-Y') . '.log';
        $log_data = $log_path.'/'.$log_filename;
        if (file_exists($log_data)) {
            $log = file_get_contents($log_data);
        }else{
            $log = "The file $log_filename does not exist";
        }
        return array(
            'log_filename' => $log_filename,
            'log_data' => $log
        );
    }

}