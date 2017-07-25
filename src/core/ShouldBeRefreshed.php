<?php

namespace blink\core;

/**
 * This interface is used to indicate that a service should be refreshed after every request, such as `blink\httpRequest`
 * and `blink\httpResponse`.
 *
 * @package blink\core
 */
interface ShouldBeRefreshed
{
}
