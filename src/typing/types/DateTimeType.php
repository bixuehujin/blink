<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class DateTimeType
 *
 * @package blink\typing\types
 */
class DateTimeType extends Type
{
    public function getName(): string
    {
        return 'datetime';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
