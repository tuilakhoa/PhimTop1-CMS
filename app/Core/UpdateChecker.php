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
        $url = 'https://api.github.com/repos/' . $this->repo . '/releases/latest';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
            'User-Agent: PhimTop1-CMS-Updater'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

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
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['tag_name'])) {
            return [
                'success' => false,
                'message' => 'Dữ liệu phản hồi từ Github không hợp lệ.'
            ];
        }

        $latestVersion = ltrim($data['tag_name'], 'v');
        $currentClean = ltrim($currentVersion, 'v');
        
        $hasUpdate = version_compare($currentClean, $latestVersion, '<');
        
        $result = [
            'success' => true,
            'current' => $currentClean,
            'latest' => $latestVersion,
            'hasUpdate' => $hasUpdate,
            'title' => $data['name'] ?? 'Bản cập nhật v' . $latestVersion,
            'description' => $data['body'] ?? 'Cập nhật từ Github Release.',
            'changelog' => $data['html_url'] ?? ('https://github.com/' . $this->repo . '/releases'),
            'download' => $data['tag_name'] // Pass the exact target tag to do_update
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
