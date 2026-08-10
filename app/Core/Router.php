<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<int, array{pattern: string, handler: mixed, middleware: array<int, string>, params: array<int, string>}>> */
    private array $routes = [
        'GET' => [], 'POST' => [], 'PUT' => [], 'PATCH' => [], 'DELETE' => [],
    ];

    private string $groupPrefix = '';
    /** @var array<int, string> */
    private array $groupMiddleware = [];

    public function get(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->add('PUT', $uri, $handler, $middleware);
    }

    public function patch(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->add('PATCH', $uri, $handler, $middleware);
    }

    public function delete(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->add('DELETE', $uri, $handler, $middleware);
    }

    /**
     * @param array{prefix?: string, middleware?: array<int, string>} $attrs
     */
    public function group(array $attrs, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $previousPrefix . ($attrs['prefix'] ?? '');
        $this->groupMiddleware = [...$previousMiddleware, ...($attrs['middleware'] ?? [])];

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    private function add(string $method, string $uri, mixed $handler, array $middleware): void
    {
        $uri = $this->groupPrefix . $uri;
        $uri = '/' . trim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        $paramNames = [];
        $pattern = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', static function (array $m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $uri);

        $this->routes[$method][] = [
            'uri' => $uri,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
            'params' => $paramNames,
        ];
    }

    /**
     * All registered routes whose path starts with the given prefix —
     * powers the admin API explorer. Handler is normalized to a readable
     * "Class@method" string.
     *
     * @return array<int, array{method: string, uri: string, handler: string, middleware: array<int, string>}>
     */
    public function routesUnder(string $prefix): array
    {
        $result = [];
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route) {
                if (!str_starts_with($route['uri'], $prefix)) {
                    continue;
                }
                $handler = is_array($route['handler'])
                    ? $route['handler'][0] . '@' . $route['handler'][1]
                    : 'Closure';
                $result[] = [
                    'method' => $method,
                    'uri' => $route['uri'],
                    'handler' => $handler,
                    'middleware' => $route['middleware'],
                ];
            }
        }
        return $result;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                $params = array_combine($route['params'], $matches);

                $pipeline = array_reduce(
                    array_reverse($route['middleware']),
                    fn (callable $next, string $spec) => fn () => $this->resolveMiddleware($spec)->handle($request, $next),
                    fn () => $this->callHandler($route['handler'], $request, $params),
                );

                $pipeline();
                return;
            }
        }

        http_response_code(404);
        echo view('errors/404');
    }

    private function resolveMiddleware(string $spec): object
    {
        $map = [
            'auth' => \App\Middleware\AuthMiddleware::class,
            'admin' => \App\Middleware\AdminMiddleware::class,
            'developer' => \App\Middleware\DeveloperMiddleware::class,
            'csrf' => \App\Middleware\CsrfMiddleware::class,
            'throttle' => \App\Middleware\ThrottleMiddleware::class,
            'maintenance' => \App\Middleware\MaintenanceMiddleware::class,
            'guest' => \App\Middleware\GuestMiddleware::class,
            'api_token' => \App\Middleware\ApiTokenMiddleware::class,
        ];

        $name = $spec;
        $args = [];
        if (str_contains($spec, ':')) {
            [$name, $argString] = explode(':', $spec, 2);
            $args = array_map(static fn (string $a) => is_numeric($a) ? (int) $a : $a, explode(',', $argString));
        }

        $class = $map[$name] ?? throw new \RuntimeException("Unknown middleware [{$name}]");

        return new $class(...$args);
    }

    private function callHandler(mixed $handler, Request $request, array $params): mixed
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->{$method}($request, ...array_values($params));
        }

        if (is_callable($handler)) {
            return $handler($request, ...array_values($params));
        }

        throw new \RuntimeException('Invalid route handler');
    }
}
