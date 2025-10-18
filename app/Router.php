<?php

declare(strict_types=1);

namespace App;

final class Router
{
    /** @var array<string, array<int, array{pattern:string, handler:callable}>> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $methodRoutes = $this->routes[$method] ?? [];

        foreach ($methodRoutes as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                ($route['handler'])(...array_values($matches));
                return;
            }
        }

        http_response_code(404);
        view('pages/error', [
            'title' => 'Página no encontrada',
            'message' => 'La ruta solicitada no existe.'
        ]);
    }

    private function addRoute(string $method, string $pattern, callable $handler): void
    {
        $regex = $this->convertPatternToRegex($pattern);
        $this->routes[$method][] = [
            'pattern' => $regex,
            'handler' => $handler,
        ];
    }

    private function convertPatternToRegex(string $pattern): string
    {
        $escaped = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $escaped . '$#';
    }
}