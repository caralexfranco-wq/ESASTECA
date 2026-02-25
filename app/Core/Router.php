<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $action, array $roles = []): void
    {
        $this->add('GET', $path, $action, $roles);
    }

    public function post(string $path, array $action, array $roles = []): void
    {
        $this->add('POST', $path, $action, $roles);
    }

    private function add(string $method, string $path, array $action, array $roles): void
    {
        $this->routes[$method][$path] = ['action' => $action, 'roles' => $roles];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim(env('BASE_PATH', ''), '/');
        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = $path ?: '/';

        $route = $this->routes[$method][$path] ?? null;
        if (!$route) {
            http_response_code(404);
            echo 'Ruta no encontrada';
            return;
        }

        if (!empty($route['roles'])) {
            if (!Auth::check()) {
                redirect('/login');
            }
            if (!Auth::hasRole($route['roles'])) {
                http_response_code(403);
                echo 'No autorizado';
                return;
            }
        }

        [$class, $methodName] = $route['action'];
        (new $class())->$methodName();
    }

    public static function url(string $path): string
    {
        $base = rtrim(env('BASE_PATH', ''), '/');
        return $base . ($path === '/' ? '' : $path);
    }
}
