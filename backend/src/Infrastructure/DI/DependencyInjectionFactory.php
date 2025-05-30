<?php

namespace App\Infrastructure\DI;
use App\Application\Command\AddEventHandler;
use App\Application\Query\GetEventDetailsHandler;
use App\Infrastructure\Http\ApiClient;
use App\Repository\EventRepositoryInterface;
use GuzzleHttp\Client as GuzzleClient;
use App\Application\Query\ListEventsHandler;
use App\Application\Service\AuthService;
use App\Infrastructure\Persistence\ApiEventRepository;
use App\Infrastructure\Persistence\RedisTokenCache;

class DependencyInjectionFactory
{
    /**
     * register all dependencies injections (singletons, bindings)
     * @return Container
     */
    public static function create() : Container
    {
        $container = New Container();

        $container->singleton(\Redis::class, function() {
            $redis = new \Redis();
            $connected = $redis->connect(REDIS_HOST, REDIS_PORT);
            if (!$connected) {
                throw new \RuntimeException("Could not connect to Redis at " . REDIS_HOST . ":" . REDIS_PORT);
            }
            return $redis;
        });

        $container->singleton(GuzzleClient::class, function() {
            return new GuzzleClient();
        });

        $container->singleton(RedisTokenCache::class, function(Container $c) {
            return new RedisTokenCache(
                $c->get(\Redis::class),
                TOKEN_CACHE_KEY,
            );
        });

        // Register Authentication Service
        $container->singleton(AuthService::class, function(Container $c) {
            return new AuthService(
                $c->get(GuzzleClient::class),
                $c->get(RedisTokenCache::class),
                API_BASE_URL . '/api/Auth',
                CLIENT_ID,
                CLIENT_SECRET
            );
        });

        $container->singleton(ApiClient::class, function(Container $c) {
            return new ApiClient(
                $c->get(GuzzleClient::class),
                $c->get(AuthService::class)
            );
        });

        $container->singleton(ApiEventRepository::class, function(Container $c) {
            return new ApiEventRepository($c->get(ApiClient::class));
        });

        $container->bind(EventRepositoryInterface::class, function(Container $c) {
            return new ApiEventRepository($c->get(ApiEventRepository::class));
        });

        $container->bind(ListEventsHandler::class, function(Container $c) {
            return new ListEventsHandler($c->get(ApiEventRepository::class));
        });

        $container->bind(GetEventDetailsHandler::class, function(Container $c) {
            return new GetEventDetailsHandler($c->get(ApiEventRepository::class));
        });
        $container->bind(AddEventHandler::class, function(Container $c) {
            return new AddEventHandler($c->get(ApiEventRepository::class)); // Or use interface if bound
        });

        return $container;
    }
}