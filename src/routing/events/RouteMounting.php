<?php

declare(strict_types=1);

namespace blink\routing\events;

use blink\routing\Router;

/**
 * Class RouteMounting
 *
 * @package blink\routing\events
 */
class RouteMounting
{
    public Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
