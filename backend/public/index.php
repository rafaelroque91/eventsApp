<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../config.php')) {
    die("File config.php doesn't exists. Copy config.php.example and fill the vars.");
}
require_once __DIR__ . '/../config.php';

use App\Infrastructure\DI\DependencyInjectionFactory;
use App\Infrastructure\Web\Router\RouterFactory;
use App\Infrastructure\Http\Middleware\CORSMiddleware;

$container = DependencyInjectionFactory::create();
$router = RouterFactory::create($container);

CORSMiddleware::setup();

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->callRoute($requestMethod, $requestUri);

?>