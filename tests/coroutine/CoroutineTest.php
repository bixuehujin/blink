<?php

namespace blink\tests\coroutine;

use blink\tests\TestCase;
use blink\coroutine\Scheduler;
use blink\coroutine\ReturnValue;


/**
 * Class GreenletTest
 * @package blink\tests\coroutine
 */
class CoroutineTest extends TestCase
{

    public function testSimple()
    {
        $scheduler = new Scheduler();

        $fn2 = function ($value) {
            yield new ReturnValue($value + 2);
        };

        $fn = function () use ($fn2) {
            $a = (yield 123);

            $b = (yield $fn2($a));

            yield new ReturnValue($b * 2);
        };

        $this->assertEquals(250, $scheduler->await($fn));
    }
}
