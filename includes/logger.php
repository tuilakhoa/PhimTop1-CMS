<?php
class Logger {
    private static $logDir = __DIR__ . '/../logs';

    public static function init() {
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0777, true);
        }
    }

    public static function log($message, $level = 'INFO') {
        self::init();
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $logFile = self::$logDir . '/app-' . $date . '.log';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
        $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN_URI';

        $formattedMessage = "[$time] [$level] [$ip] [$uri] $message" . PHP_EOL;
        @file_put_contents($logFile, $formattedMessage, FILE_APPEND);
        
        // Auto cleanup logs older than 7 days (2% chance to trigger)
        if (rand(1, 50) === 1) {
            $files = glob(self::$logDir . '/app-*.log');
            if ($files) {
                $now = time();
                foreach ($files as $file) {
                    if ($now - filemtime($file) >= 7 * 86400) { // 7 days
                        @unlink($file);
                    }
                }
            }
        }
    }

    public static function info($message) {
        self::log($message, 'INFO');
    }

    public static function warning($message) {
        self::log($message, 'WARNING');
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }

    public static function critical($message) {
        self::log($message, 'CRITICAL');
    }
}

// Error Handler setup
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $message = "$errstr in $errfile on line $errline";
    switch ($errno) {
        case E_USER_ERROR:
            Logger::critical($message);
            break;
        case E_USER_WARNING:
        case E_WARNING:
            Logger::warning($message);
            break;
        case E_USER_NOTICE:
        case E_NOTICE:
            Logger::info($message);
            break;
        default:
            Logger::error($message);
            break;
    }
    return true; // Prevent default PHP error handler
}

function custom_exception_handler($exception) {
    Logger::critical("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\nTrace: " . $exception->getTraceAsString());
}
