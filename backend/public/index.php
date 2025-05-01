<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use App\Infrastructure\DI\DependencyInjectionFactory;
use App\Infrastructure\Web\Router\RouterFactory;

$container = DependencyInjectionFactory::create();
$router = RouterFactory::create($container);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->callRoute($requestMethod, $requestUri);

?>