<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;
use RuntimeException;

/**
 * Class IntegerType
 *
 * @package blink\typing\types
 */
class IntegerType extends Type
{
    public function __construct(
        /**
         * The width of the integer, in bits.
         */
        protected ?int $width = null,
        /**
         * Whether the integer is unsigned.
         */
        protected ?bool $unsigned = null
    ) {}

    public function getName(): string
    {
        if ($this->width === null) {
            return 'integer';
        }

        $type = match ($this->width) {
            8 => $name = 'int8',
            16 => $name = 'int16',
            32 => $name = 'int32',
            64 => $name = 'int64',
            default => throw new RuntimeException("Invalid integer width: $this->width"),
        };

        return $this->unsigned ? 'u' . $type : $type;
    }

    /**
     * Re
     *
     * @return int
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function isUnsigned(): bool
    {
        return !!$this->unsigned;
    }

    public function getMetadata(): array
    {
        return [];
    }
}
