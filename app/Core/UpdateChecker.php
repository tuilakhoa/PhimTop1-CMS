<?php
namespace App\Core;

class UpdateChecker {
    private $config;
    private $cacheFile;
    private $cacheTime = 43200; // 12 hours in seconds

    public function __construct($updateServerUrl = null) {
        $configFile = __DIR__ . '/../../config/update.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = [
                'current_version' => '1.0.0',
            ];
        }
        $this->config['update_server'] = $updateServerUrl ?: ($this->config['update_server'] ?? 'https://update.phimtop1.asia/check');
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
        $url = $this->config['update_server'] . '?v=' . urlencode($currentVersion);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: PlayCMS'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing, better to enable in production

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối máy chủ cập nhật.'
            ];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['success'])) {
            return [
                'success' => false,
                'message' => 'Dữ liệu phản hồi không hợp lệ.'
            ];
        }

        if ($data['success']) {
            $latestVersion = $data['latest'] ?? $currentVersion;
            // Use version_compare
            $hasUpdate = version_compare($currentVersion, $latestVersion, '<');
            $data['hasUpdate'] = $hasUpdate;
            $data['current'] = $currentVersion;
            
            $this->setCache($data);
        }

        return $data;
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
