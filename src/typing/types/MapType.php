<?php

declare(strict_types=1);

namespace blink\typing\types;

/**
 * Class MapType
 *
 * @package blink\typing\types
 */
class MapType extends GenericType
{
    public function getName(): string
    {
        return 'map';
    }

    public function allowedParameters(): int
    {
        return 2;
    }
}
