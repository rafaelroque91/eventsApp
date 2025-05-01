<?php

declare(strict_types=1);

// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration (adjust path if needed)
require_once __DIR__ . '/../config.php';

//use Predis\Client as RedisClient;
use App\Infrastructure\DI\DependencyInjectionFactory;
use App\Infrastructure\Web\Router\RouterFactory;

// Manage the Dependency Injections
$container = DependencyInjectionFactory::create();

//Manage Routes
$router = RouterFactory::create($container);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
try {
    $router->callRoute($requestMethod, $requestUri);
} catch (\App\Infrastructure\Web\Router\RouteNotFoundException $e) {
    http_response_code(404);
    echo "404 Not Found: " . htmlspecialchars($requestUri);
} catch (\Throwable $e) {
    error_log("Unhandled exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    // You can render a nice 500 error template here
    echo "500 Internal Server Error. Check logs for details.".$e->getMessage() . "\n" . htmlspecialchars($e->getTraceAsString());
    // Optionally display more details in development
    if (ini_get('display_errors')) {
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}

?>