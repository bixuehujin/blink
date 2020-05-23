<?php

declare(strict_types=1);

namespace blink\routing;

use blink\eventbus\EventBus;
use blink\injector\Container;
use blink\routing\events\RouteMounting;
use blink\injector\ServiceProvider;

/**
 * Class RouteServiceProvider
 *
 * @package blink\routing
 */
abstract class RouteServiceProvider extends ServiceProvider
{
    abstract public function mount(Router $router): void;

    public function register(Container $container): void
    {
        $bus = $container->get(EventBus::class);
        $bus->attach(RouteMounting::class, function () use ($container) {
            $router = $container->get(Router::class);
            $this->mount($router);
        });
    }
}
