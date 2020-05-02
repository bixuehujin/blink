<?php

declare(strict_types=1);

namespace blink\routing\exceptions;

use blink\core\Exception;
use Throwable;

/**
 * Class MethodNotAllowedException
 *
 * @package blink\routing\exceptions
 */
class MethodNotAllowedException extends Exception
{
    protected array $allowedMethods = [];

    public function __construct(string $message, array $allowedMethods, $code = 0, Throwable $previous = null)
    {
        $this->allowedMethods = $allowedMethods;
        parent::__construct($message, $code, $previous);
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
