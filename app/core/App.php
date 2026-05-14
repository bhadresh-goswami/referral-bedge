<?php

declare(strict_types=1);

namespace App\Core;

class App
{
    public function __construct(private Router $router)
    {
    }

    public function run(): mixed
    {
        return $this->router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    }
}
