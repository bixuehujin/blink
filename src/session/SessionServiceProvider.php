<?php

declare(strict_types=1);

namespace blink\session;

use blink\di\Container;
use blink\di\ServiceProvider;
use blink\di\config\ConfigContainer;

/**
 * Class SessionServiceProvider
 *
 * @package blink\session
 */
class SessionServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        $config = $container->get(ConfigContainer::class);
        $config->define('session.path')->required();
    }
}
