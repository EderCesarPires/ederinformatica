<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Roteador simples com suporte a parâmetros: /clients/{id}/edit
 */
final class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $path => [$class, $action]) {
            $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $path) . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
                $controller = new $class();
                $controller->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Página não encontrada'], 'auth');
    }
}
