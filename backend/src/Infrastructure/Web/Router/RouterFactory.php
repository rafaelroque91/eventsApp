<?php

namespace App\Infrastructure\Web\Router;

use App\Infrastructure\DI\Container;
use App\Infrastructure\Web\Controller\EventController;

class RouterFactory
{
    public static function create(Container $container): Router
    {
        $router = new Router($container);

        // API Routes
        $router->addAPIRoute('GET', '/events', EventController::class, 'list');
        $router->addAPIRoute('POST', '/events', EventController::class, 'store');
        $router->addAPIRoute('GET', '/events/([^/]+)', EventController::class, 'show'); // Show detail page

        return $router;
    }
}