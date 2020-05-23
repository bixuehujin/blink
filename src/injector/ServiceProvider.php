<?php

declare(strict_types=1);

namespace blink\injector;

/**
 * Class ServiceProvider
 *
 * @package blink\kernel
 */
abstract class ServiceProvider
{
    /**
     * @param Container $container
     * @return void
     */
    abstract public function register(Container $container): void;
}
