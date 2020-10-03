<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class NullType
 *
 * @package blink\typing\types
 */
class NullType extends Type
{
    public function getName(): string
    {
        return 'null';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
