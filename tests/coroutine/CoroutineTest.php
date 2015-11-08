<?php

namespace blink\tests\coroutine;

use blink\core\Exception;
use blink\tests\TestCase;
use blink\coroutine\Scheduler;
use blink\coroutine\ReturnValue;

/**
 * Class CoroutineTest
 *
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

    public function testSimpleWithoutReturn()
    {
        $scheduler = new Scheduler();

        $fn = function () {
            yield 1;
            yield 2;
        };

        $this->assertNull($scheduler->await($fn));
    }

    public function testPromise()
    {
        $fn = function () {
            $a = (yield \React\Promise\resolve(1));

            yield new ReturnValue($a * 2);
        };

        $scheduler = new Scheduler();
        $this->assertEquals(2, $scheduler->await($fn));
    }

    public function testPromiseWithException()
    {
        $fn = function () {
            $reason = new Exception('the reason');

            try {
                yield \React\Promise\reject($reason);
            } catch (\Exception $e) {
                $this->assertSame($reason, $e);
                yield new ReturnValue($e);
            }
        };

        $scheduler = new Scheduler();
        $this->assertInstanceOf(Exception::class, $scheduler->await($fn));
    }
}
