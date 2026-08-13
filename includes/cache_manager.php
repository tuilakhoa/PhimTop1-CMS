<?php
class CacheManager {
    private $cacheDir;

    public function __construct($dir = __DIR__ . '/../cache/api') {
        $this->cacheDir = $dir;
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get($key, $ttl = 900) {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            if ((time() - filemtime($cacheFile)) < $ttl) {
                return file_get_contents($cacheFile);
            }
        }
        return null;
    }

    public function getStale($key) {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile);
        }
        return null;
    }

    public function set($key, $data) {
        $cacheFile = $this->getCacheFilePath($key);
        file_put_contents($cacheFile, $data);
    }

    public function delete($key) {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    public function clearExpired($ttl = 86400) {
        $files = glob($this->cacheDir . '/*.json');
        if ($files) {
            $now = time();
            foreach ($files as $file) {
                if ($now - filemtime($file) >= $ttl) {
                    @unlink($file);
                }
            }
        }
    }

    public function clearAll() {
        $files = glob($this->cacheDir . '/*.json');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    private function getCacheFilePath($key) {
        return $this->cacheDir . '/' . md5($key) . '.json';
    }
}
