<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class IntegerType
 *
 * @package blink\typing\types
 */
class IntegerType extends Type
{
    public function getName(): string
    {
        return 'integer';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
