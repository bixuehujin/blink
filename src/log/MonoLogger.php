<?php

declare(strict_types=1);

namespace blink\log;

use blink\core\BaseObject;
use Monolog\Logger as BaseMonoLogger;
use Monolog\Handler\HandlerInterface;

/**
 * Class MonoLogger
 *
 * @package blink\log
 */
class MonoLogger extends BaseMonoLogger
{
    /**
     * Hack to remove the default logger support of Monolog.
     */
    public function pushHandler(HandlerInterface $handler)
    {
        if ($handler instanceof BaseObject) {
            array_unshift($this->handlers, $handler);
        }

        return $this;
    }
}
