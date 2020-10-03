<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class StringType
 *
 * @package blink\typing\types
 */
class StringType extends Type
{
    public function getName(): string
    {
        return 'string';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
