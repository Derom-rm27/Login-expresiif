<?php

declare(strict_types=1);

namespace App;

final class Router
{
    /** @var array<string, array<int, array{pattern:string, controller:class-string, method:string}>> */
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $methodRoutes = $this->routes[$method] ?? [];

        foreach ($methodRoutes as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remover el match completo
                
                // Convertir parámetros numéricos a int cuando sea posible
                $params = [];
                foreach ($matches as $param) {
                    if (is_numeric($param)) {
                        $params[] = (int) $param;
                    } else {
                        $params[] = $param;
                    }
                }
                
                $controller = new $route['controller']();
                call_user_func_array([$controller, $route['method']], $params);
                return;
            }
        }

        http_response_code(404);
        view('pages/error', [
            'title' => 'Página no encontrada',
            'message' => 'La ruta solicitada no existe.'
        ]);
    }

    private function addRoute(string $method, string $pattern, array $handler): void
    {
        $regex = $this->convertPatternToRegex($pattern);
        $this->routes[$method][] = [
            'pattern' => $regex,
            'controller' => $handler[0],
            'method' => $handler[1],
        ];
    }

    private function convertPatternToRegex(string $pattern): string
    {
        $escaped = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $escaped . '$#';
    }
}