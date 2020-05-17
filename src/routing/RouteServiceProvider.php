<?php

declare(strict_types=1);

namespace blink\routing;

use blink\eventbus\EventBus;
use blink\kernel\events\RouteMounting;
use blink\kernel\Kernel;
use blink\kernel\ServiceProvider;

/**
 * Class RouteServiceProvider
 *
 * @package blink\routing
 */
abstract class RouteServiceProvider extends ServiceProvider
{
    abstract public function mount(Router $router): void;

    /**
     * @param Kernel $kernel
     * @return void
     */
    public function register($kernel): void
    {
        $bus = $kernel->getContainer()->get(EventBus::class);
        $bus->attach(RouteMounting::class, function () use ($kernel) {
            $router = $kernel->get(Router::class);
            $this->mount($router);
        });
    }
}
