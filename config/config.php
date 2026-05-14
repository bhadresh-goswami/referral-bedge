<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$basePath = dirname(__DIR__);

if (class_exists(Dotenv::class) && is_file($basePath . '/.env')) {
    Dotenv::createImmutable($basePath)->safeLoad();
}

if (!defined('APP_NAME')) {
    define('APP_NAME', $_ENV['APP_NAME'] ?? $_SERVER['APP_NAME'] ?? 'My App');
}

if (!defined('APP_URL')) {
    define('APP_URL', $_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? 'http://localhost');
}

if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'app');
}

if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');
}

if (!defined('UPLOAD_MAX_SIZE')) {
    define('UPLOAD_MAX_SIZE', (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? $_SERVER['UPLOAD_MAX_SIZE'] ?? 5242880));
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', $basePath);
}

if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', $_ENV['STORAGE_PATH'] ?? $_SERVER['STORAGE_PATH'] ?? BASE_PATH . '/storage');
}

if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', $_ENV['UPLOAD_PATH'] ?? $_SERVER['UPLOAD_PATH'] ?? STORAGE_PATH . '/uploads');
}

if (!defined('LOG_PATH')) {
    define('LOG_PATH', $_ENV['LOG_PATH'] ?? $_SERVER['LOG_PATH'] ?? STORAGE_PATH . '/logs');
}
