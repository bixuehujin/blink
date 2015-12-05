<?php

namespace blink\core;

/**
 * Interface MiddlewareContract
 *
 * @package blink\http
 */
interface MiddlewareContract
{
    public function handle($owner);
}
