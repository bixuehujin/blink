<?php

declare(strict_types=1);

namespace blink\kernel;

abstract class ServiceProvider
{
    /**
     * @param Kernel $kernel
     * @return mixed
     */
    abstract public function register($kernel);
}
