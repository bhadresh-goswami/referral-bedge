<?php

namespace App\Core;

class Controller
{
    public function view($view, $data = [])
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            echo 'View file not found.';
            return;
        }

        extract($data);
        require $viewPath;
    }
}
