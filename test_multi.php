<?php
function multiRequestWithRetry($urls, $max_retries = 5) {
    $results = [];
    $failed_urls = $urls;
    $attempt = 1;
    echo "In multiRequestWithRetry\n";

    while (!empty($failed_urls) && $attempt <= $max_retries) {
        $multi = curl_multi_init();
        $channels = [];
        foreach ($failed_urls as $key => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
            curl_multi_add_handle($multi, $ch);
            $channels[$key] = $ch;
        }
        
        $active = null;
        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) {
                curl_multi_select($multi);
            }
        } while ($active && $status == CURLM_OK);
        
        echo "Multi exec done, status $status, active $active\n";
        
        $new_failed = [];
        foreach ($channels as $key => $ch) {
            $res = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $parsed = json_decode($res, true);
            if ($httpCode >= 200 && $httpCode < 300 && $parsed && (isset($parsed['data']['item']) || isset($parsed['movie']))) {
                $results[$key] = $parsed;
            } else {
                $new_failed[$key] = $failed_urls[$key];
            }
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }
        curl_multi_close($multi);
        $failed_urls = $new_failed;
        $attempt++;
    }
    return $results;
}

$urls = ["slug1" => "https://phimapi.com/v1/api/phim/doraemon"];
$res = multiRequestWithRetry($urls, 2);
var_dump(array_keys($res));
