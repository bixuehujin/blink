<?php

declare(strict_types=1);

namespace blink\routing;

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
        /** @var Router $router */
        $router = $kernel->get('router');

        $this->mount($router);
    }
}
