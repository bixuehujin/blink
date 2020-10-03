<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class StructType
 *
 * @package blink\typing\types
 */
class StructType extends Type
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): array
    {
        return [];
    }

    /**
     * @return Type[]
     */
    public function columns(): array
    {
        return [];
    }
}
