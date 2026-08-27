<?php
class CacheManager {
    private $cacheDir;
    private $redis = null;
    private $memcached = null;
    private $useFileCache = true;

    public function __construct($dir = __DIR__ . '/../cache/api') {
        $this->cacheDir = $dir;
        
        // Cố gắng kết nối Redis để giảm tải I/O cho ổ cứng (Thường có trên aaPanel)
        if (class_exists('Redis')) {
            try {
                $redis = new Redis();
                if (@$redis->connect('127.0.0.1', 6379, 1)) {
                    $this->redis = $redis;
                    $this->useFileCache = false;
                }
            } catch (Exception $e) {}
        }
        
        // Cố gắng kết nối Memcached nếu không có Redis
        if ($this->useFileCache && class_exists('Memcached')) {
            $memcached = new Memcached();
            $memcached->addServer('127.0.0.1', 11211);
            $stats = @$memcached->getStats();
            if (!empty($stats)) {
                $this->memcached = $memcached;
                $this->useFileCache = false;
            }
        }
        
        if ($this->useFileCache) {
            if (!is_dir($this->cacheDir)) {
                @mkdir($this->cacheDir, 0777, true);
            }
            // Tự động dọn rác (GC): 2% xác suất dọn dẹp file cache cũ mỗi lần khởi tạo
            if (rand(1, 50) === 1) {
                $this->clearExpired(86400);
            }
        }
    }

    public function get($key, $ttl = 900) {
        $hashKey = md5($key);
        
        if ($this->redis) {
            $data = $this->redis->get("phimtop1_cache_" . $hashKey);
            return $data !== false ? $data : null;
        }
        if ($this->memcached) {
            $data = $this->memcached->get("phimtop1_cache_" . $hashKey);
            return $data !== false ? $data : null;
        }
        
        // File Cache Fallback
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            if ((time() - filemtime($cacheFile)) < $ttl) {
                return file_get_contents($cacheFile);
            }
        }
        return null;
    }

    public function getStale($key) {
        $hashKey = md5($key);
        
        if ($this->redis) {
            $data = $this->redis->get("phimtop1_cache_" . $hashKey); // Redis usually deletes on expire, so stale might not exist unless manually managed
            return $data !== false ? $data : null;
        }
        if ($this->memcached) {
            $data = $this->memcached->get("phimtop1_cache_" . $hashKey);
            return $data !== false ? $data : null;
        }
        
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile);
        }
        return null;
    }

    public function set($key, $data, $ttl = 900) {
        $hashKey = md5($key);
        
        if ($this->redis) {
            $this->redis->set("phimtop1_cache_" . $hashKey, $data, $ttl);
            return;
        }
        if ($this->memcached) {
            $this->memcached->set("phimtop1_cache_" . $hashKey, $data, $ttl);
            return;
        }
        
        $cacheFile = $this->getCacheFilePath($key);
        file_put_contents($cacheFile, $data);
    }

    public function delete($key) {
        $hashKey = md5($key);
        
        if ($this->redis) {
            $this->redis->del("phimtop1_cache_" . $hashKey);
            return;
        }
        if ($this->memcached) {
            $this->memcached->delete("phimtop1_cache_" . $hashKey);
            return;
        }
        
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    public function clearExpired($ttl = 86400) {
        if (!$this->useFileCache) return; // Redis/Memcached auto-expire
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
        if ($this->redis) {
            // Can't easily flush only specific prefix without keys(), so we ignore clearAll for mem/redis here unless we build a pattern, 
            // but for file it's fine.
            return;
        }
        if ($this->memcached) return;
        
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
