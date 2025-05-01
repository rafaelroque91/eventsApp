<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Router;

use App\Infrastructure\DI\Container;
use LogicException;


//todo move later
class RouteNotFoundException extends \Exception {}

class Router
{
    private array $routes = [];

    // Add constructor injection for the Container
    public function __construct(
        private readonly Container $container)
    {}

    public function addRoute(string $method, string $path, string $controller, string $functionName): void
    {
        // Basic regex conversion for parameters like /events/{id} -> /events/(\d+)
        // More robust routing would use named parameters and type hints
//        $pathRegex = preg_replace('/\{(\w+)\}/', '([^/]+)', $controller);
        $this->routes[$method][$path] = [$controller, $functionName];
    }

    public function addAPIRoute(string $method, string $path, string $controller, string $functionName, string $apiVersion = 'v1'): void
    {
        $this->addRoute($method, '/api/'.$apiVersion.$path, $controller, $functionName);
    }

    public function callRoute(string $method, string $uri): void
    {
        //separate uri from query string
        $uri = parse_url($uri, PHP_URL_PATH) ? : '/';
        //eu acho q nao precisa
//        $queryString = parse_url($uri, PHP_URL_QUERY) ?: '';

        // Check if any routes are defined for this method
//        if (!isset($this->routes[$method])) {
//            throw new RouteNotFoundException("No routes defined for method {$method}");
//        }

        foreach ($this->routes[$method] as $pathRegex => $callable) {
            if (preg_match('#^' . $pathRegex . '$#', $uri, $matches)) {
                array_shift($matches); //get the route parameter (if exists)

                $this->dispatchControllerFunction($callable[0],$callable[1], $matches);
                return;
            }
        }

        throw new RouteNotFoundException("No route found for {$method} {$uri}");
    }

    private function dispatchControllerFunction(string $controller, string $method, mixed $param) : void
    {
        try {
            $controllerInstance = $this->container->get($controller);
        } catch (\Psr\Container\NotFoundExceptionInterface | \Psr\Container\ContainerExceptionInterface $e) {
            // Catch container-specific exceptions during resolution
            error_log("DI Container error resolving {$controller}: " . $e->getMessage());
            throw new LogicException("Could not create controller '{$controller}'. Check container configuration and dependencies.", 0, $e);
        }

        if (!method_exists($controllerInstance, $method)) {
            throw new LogicException("Method {$method} does not exist on controller {$controller}");
        }

        if (is_callable([$controllerInstance, $method])) {
            call_user_func_array([$controllerInstance, $method], $param);
        } else {
            throw new \LogicException("Invalid controller function {$controller}. Method {$method}");
        }
    }
}