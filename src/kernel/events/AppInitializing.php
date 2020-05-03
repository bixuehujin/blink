<?php

declare(strict_types=1);

namespace blink\kernel\events;

use blink\kernel\Kernel;

/**
 * Class AppInitializing
 *
 * @package blink\kernel\events
 */
class AppInitializing
{
    public Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
}
