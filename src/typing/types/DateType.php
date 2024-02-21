<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * DateType
 *
 * @package blink\typing\types
 */
class DateType extends Type
{
    public function getName(): string
    {
        return 'Date';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
