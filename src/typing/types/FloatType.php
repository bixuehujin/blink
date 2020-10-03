<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class FloatType
 *
 * @package blink\typing\types
 */
class FloatType extends Type
{
    public function getName(): string
    {
        return 'float';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
