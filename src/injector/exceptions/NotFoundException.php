<?php

declare(strict_types=1);

namespace blink\injector\exceptions;


use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * @package blink\injector
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{

}
