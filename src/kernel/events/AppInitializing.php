<?php

declare(strict_types=1);

namespace blink\kernel\events;

use blink\injector\Container;

/**
 * Class AppInitializing
 *
 * @package blink\kernel\events
 */
class AppInitializing
{
    public Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
