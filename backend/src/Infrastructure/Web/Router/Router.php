<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Router;

use App\Application\Validation\RequestUtilTrait;
use App\Infrastructure\DI\Container;
use LogicException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;


class Router
{
    use RequestUtilTrait;

    private array $routes = [];

    public function __construct(
        private readonly Container $container)
    {}

    /**
     * Add routes and relates to controller / method
     * @param string $method
     * @param string $path
     * @param string $controller
     * @param string $functionName
     * @return void
     */
    public function addRoute(string $method, string $path, string $controller, string $functionName): void
    {
        $this->routes[$method][$path] = [$controller, $functionName];
    }

    /**
     *  Add API routes and relates to controller / method
     * @param string $method
     * @param string $path
     * @param string $controller
     * @param string $functionName
     * @param string $apiVersion
     * @return void
     */
    public function addAPIRoute(string $method, string $path, string $controller, string $functionName, string $apiVersion = 'v1'): void
    {
        $this->addRoute($method, '/api/'.$apiVersion.$path, $controller, $functionName);
    }

    /**
     * Resolve route -> controller / method
     * @param string $method
     * @param string $uri
     * @return void
     */
    public function callRoute(string $method, string $uri): void
    {
        try {
            $uri = parse_url($uri, PHP_URL_PATH) ?: '/';

            foreach ($this->routes[$method] as $pathRegex => $callable) {
                if (preg_match('#^' . $pathRegex . '$#', $uri, $matches)) {
                    array_shift($matches); //get the route parameter (if exists)

                    $this->dispatchControllerFunction($callable[0], $callable[1], $matches);
                    return;
                }
            }

            $this->jsonResponseNotFound("No route found for {$method} {$uri}");

        } catch (\Throwable $e) {
            $this->jsonErrorResponse($e);
        }
    }

    /**
     * Get controller, resolve dependency injections and call the related method
     * @param string $controller
     * @param string $method
     * @param mixed $param
     * @return void
     */
    private function dispatchControllerFunction(string $controller, string $method, mixed $param) : void
    {
        try {
            $controllerInstance = $this->container->get($controller);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            throw new LogicException("Could not create controller '{$controller}'. Check container configuration and dependencies.", 0, $e);
        }

        if (!method_exists($controllerInstance, $method)) {
            throw new LogicException("Method {$method} does not exist on controller {$controller}");
        }

        if (is_callable([$controllerInstance, $method])) {
            echo call_user_func_array([$controllerInstance, $method], $param);
        } else {
            throw new \LogicException("Invalid controller function {$controller}. Method {$method}");
        }
    }
}