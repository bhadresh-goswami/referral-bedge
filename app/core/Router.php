<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /**
     * @var array<string, array<string, callable|array{0: class-string, 1: string}>>
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    /**
     * @param callable|array{0: class-string, 1: string} $callback
     */
    public function get(string $uri, callable|array $callback): void
    {
        $this->routes['GET'][$this->normalizeUri($uri)] = $callback;
    }

    /**
     * @param callable|array{0: class-string, 1: string} $callback
     */
    public function post(string $uri, callable|array $callback): void
    {
        $this->routes['POST'][$this->normalizeUri($uri)] = $callback;
    }

    public function dispatch(string $requestUri, string $requestMethod): mixed
    {
        $method = strtoupper($requestMethod);
        $uri = $this->normalizeUri($requestUri);

        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            return '404 Not Found';
        }

        $callback = $this->routes[$method][$uri];

        if (is_array($callback) && isset($callback[0], $callback[1]) && is_string($callback[0]) && is_string($callback[1])) {
            $controller = new $callback[0]();
            return $controller->{$callback[1]}();
        }

        return $callback();
    }

    private function normalizeUri(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
    }
}
