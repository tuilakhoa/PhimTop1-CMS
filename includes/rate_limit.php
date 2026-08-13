<?php
require_once __DIR__ . '/cache_manager.php';
require_once __DIR__ . '/logger.php';

class RateLimiter {
    private $cache;
    
    public function __construct() {
        $this->cache = new CacheManager(__DIR__ . '/../cache/rate_limit');
    }

    /**
     * @param string $action Example: 'api_global', 'login'
     * @param int $maxRequests Max requests allowed
     * @param int $windowSeconds Time window in seconds
     * @return bool True if allowed, False if limit exceeded
     */
    public function checkLimit($action, $maxRequests = 60, $windowSeconds = 60) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
        if ($ip === 'UNKNOWN_IP' || $ip === '127.0.0.1' || $ip === '::1') {
            return true; // Bypass localhost
        }

        $key = $action . '_' . $ip;
        
        $data = $this->cache->get($key, $windowSeconds);
        if ($data) {
            $record = json_decode($data, true);
            if ($record['count'] >= $maxRequests) {
                Logger::warning("Rate limit exceeded for IP: $ip on action: $action");
                return false;
            }
            $record['count']++;
            $this->cache->set($key, json_encode($record));
        } else {
            $record = [
                'count' => 1,
                'start_time' => time()
            ];
            $this->cache->set($key, json_encode($record));
        }
        
        return true;
    }
}

function checkRateLimit() {
    $limiter = new RateLimiter();
    // Default limit: 60 requests per minute per IP for API
    if (!$limiter->checkLimit('api_global', 60, 60)) {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'Too Many Requests. Please try again later.'
        ]);
        exit;
    }
}
