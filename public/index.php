<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use App\Core\App;
use App\Core\Router;

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$basePath = dirname(__DIR__);

$autoloadPath = $basePath . '/vendor/autoload.php';
$configPath = $basePath . '/config/config.php';
$routesPath = $basePath . '/routes/web.php';

if (!is_file($autoloadPath)) {
    throw new RuntimeException('Autoload file not found at: ' . $autoloadPath);
}

if (!is_file($configPath)) {
    throw new RuntimeException('Config file not found at: ' . $configPath);
}

if (!is_file($routesPath)) {
    throw new RuntimeException('Routes file not found at: ' . $routesPath);
}

require $autoloadPath;
require $configPath;

$router = new Router();

require $routesPath;

$app = new App($router);
$app->run();
