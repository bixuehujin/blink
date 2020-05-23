<?php

declare(strict_types=1);

namespace blink\di\exceptions;


use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * @package blink\di
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{

}
