<?php

declare(strict_types=1);

namespace blink\database\schema\attributes;

use Attribute;

#[Attribute]
class Column
{
    public function __construct(
        public ?string $name = null,
        public ?string $type = null,
        public ?int $length = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public bool $nullable = false,
        public mixed $default = null,
        public ?string $label = null,
        public ?string $comment = null,
    ) {
    }
}
