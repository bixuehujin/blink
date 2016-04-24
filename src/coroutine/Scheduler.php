<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\coroutine;

use blink\core\Object;

/**
 * Class Scheduler
 *
 * @package blink\coroutine
 */
class Scheduler extends Object
{
    public function await($task)
    {
        if (is_callable($task)) {
            $task = $task();
        }

        $let = new Coroutine($task);
        return $let->run();
    }
}
