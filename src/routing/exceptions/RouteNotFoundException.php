<?php

declare(strict_types=1);

namespace blink\routing\exceptions;

use blink\core\Exception;
use Throwable;

/**
 * Class RouteNotFoundException
 *
 * @package blink\routing\exceptions
 */
class RouteNotFoundException extends Exception
{
    public function __construct($path = "", $code = 0, Throwable $previous = null)
    {
        $message = "Route not found, no route to {$path}";
        parent::__construct($message, $code, $previous);
    }
}
