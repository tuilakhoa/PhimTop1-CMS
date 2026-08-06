<?php
namespace App\Core;

class UpdateChecker {
    private $config;
    private $cacheFile;
    private $cacheTime = 43200; // 12 hours in seconds
    private $repo;

    public function __construct() {
        $configFile = __DIR__ . '/../../config/update.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = [
                'current_version' => '1.0.0',
            ];
        }
        
        $this->repo = 'tuilakhoa/PhimTop1-CMS';
        
        $this->cacheFile = __DIR__ . '/../../config/.update_cache.json';
    }

    public function getCurrentVersion() {
        return $this->config['current_version'] ?? '1.0.0';
    }

    public function check($force = false) {
        if (!$force && $this->hasValidCache()) {
            return $this->getCache();
        }

        $currentVersion = $this->getCurrentVersion();
        $url = 'https://api.github.com/repos/' . $this->repo . '/releases';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
            'User-Agent: PhimTop1-CMS-Updater'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối máy chủ Github (Mã lỗi: ' . $httpCode . ')'
            ];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data)) {
            return [
                'success' => false,
                'message' => 'Dữ liệu phản hồi từ Github không hợp lệ hoặc chưa có phiên bản nào.'
            ];
        }

        $currentClean = ltrim($currentVersion, 'v');
        $latestVersion = ltrim($data[0]['tag_name'], 'v');
        $hasUpdate = version_compare($currentClean, $latestVersion, '<');

        $releases = [];
        foreach (array_slice($data, 0, 10) as $release) {
            $releases[] = [
                'tag_name' => $release['tag_name'],
                'version' => ltrim($release['tag_name'], 'v'),
                'title' => $release['name'] ?? 'Bản cập nhật ' . $release['tag_name'],
                'description' => $release['body'] ?? '',
                'changelog' => $release['html_url'] ?? ('https://github.com/' . $this->repo . '/releases/tag/' . $release['tag_name'])
            ];
        }

        $result = [
            'success' => true,
            'current' => $currentClean,
            'latest' => $latestVersion,
            'hasUpdate' => $hasUpdate,
            'releases' => $releases
        ];

        $this->setCache($result);
        return $result;
    }

    public function clearCache() {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    private function hasValidCache() {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $mtime = filemtime($this->cacheFile);
        if (time() - $mtime > $this->cacheTime) {
            return false;
        }
        
        return true;
    }

    private function getCache() {
        $content = file_get_contents($this->cacheFile);
        return json_decode($content, true);
    }

    private function setCache($data) {
        file_put_contents($this->cacheFile, json_encode($data));
    }
}
