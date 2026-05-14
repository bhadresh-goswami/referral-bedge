<?php

declare(strict_types=1);

use App\Controllers\ReferralController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [ReferralController::class, 'index']);
$router->post('/submit-referral', [ReferralController::class, 'submit']);
