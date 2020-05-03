<?php

declare(strict_types=1);

namespace blink\kernel\events;

use blink\routing\Router;

/**
 * Class RouteMounting
 *
 * @package blink\kernel\events
 */
class RouteMounting
{
    public Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
