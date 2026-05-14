<?php

declare(strict_types=1);

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

require $basePath . '/vendor/autoload.php';
require $basePath . '/config/config.php';

$router = new Router();

require $basePath . '/routes/web.php';

$app = new App($router);
$app->run();
