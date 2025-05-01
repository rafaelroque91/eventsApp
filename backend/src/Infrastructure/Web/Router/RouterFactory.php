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
        $router->addAPIRoute('GET', '/events', EventController::class, 'listApi');
        $router->addAPIRoute('POST', '/events', EventController::class, 'store');
        $router->addAPIRoute('GET', '/events/([^/]+)', EventController::class, 'show'); // Show detail page


        //todo remove it later
//        $router->addRoute('GET', '/', EventController::class, 'index'); // Show list page
//        $router->addRoute('GET', '/events/(\d+)', EventController::class, 'show'); // Show detail page
//        $router->addRoute('GET', '/events/add', EventController::class, 'create'); // Show add form page

        return $router;
    }
}