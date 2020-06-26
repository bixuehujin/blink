<?php

declare(strict_types=1);

namespace blink\logging;

use blink\di\config\ConfigContainer;
use blink\di\Container;
use blink\di\ServiceProvider;
use Psr\Log\LogLevel;

/**
 * Class LoggerServiceProvider
 *
 * @package blink\logging
 */
class LoggerServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        $store = $container->get(ConfigContainer::class);
        $store->define('logger.name')->required();
        $store->define('logger.log_file')->required();
        $store->define('logger.log_level')->default(LogLevel::INFO);
    }
}
