<?php

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class AnyType
 *
 * @package blink\typing\types
 */
class AnyType extends Type
{
    public function getName(): string
    {
        return 'any';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
