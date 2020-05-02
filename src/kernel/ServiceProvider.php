<?php

declare(strict_types=1);

namespace blink\kernel;

abstract class ServiceProvider
{
    /**
     * @param Kernel $kernel
     * @return void
     */
    abstract public function register($kernel): void;
}
