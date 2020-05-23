<?php

declare(strict_types=1);

namespace blink\console\events;

use blink\console\Application;

/**
 * Class CommandRegistering
 *
 * @package blink\console\events
 */
class CommandRegistering
{
    public Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}
