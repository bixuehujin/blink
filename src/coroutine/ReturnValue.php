<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\coroutine;

/**
 * ReturnValue represents a value that returned by a Generator.
 *
 * @package blink\coroutine
 */
class ReturnValue
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
