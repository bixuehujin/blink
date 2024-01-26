<?php

declare(strict_types=1);

namespace blink\typing\types;

use RuntimeException;
use blink\typing\Type;

/**
 * The Float Data Type
 *
 * @package blink\typing\types
 */
class FloatType extends Type
{
    public function __construct(
        protected ?int $width = null,
    ) {
    }

    public function getName(): string
    {
        if ($this->width === null) {
            return 'float';
        } elseif ($this->width === 32 || $this->width === 64) {
            return 'float' . $this->width;
        } else {
            throw new RuntimeException("Invalid float width: $this->width");
        }
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getMetadata(): array
    {
        return [];
    }
}
