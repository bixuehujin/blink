<?php

declare(strict_types=1);

namespace blink\database\schema\attributes;

use Attribute;

#[Attribute]
class HasOne
{
    public function __construct(
        public string $target,
        public ?string $name = null,
        public ?string $foreignKey = null,
        public ?string $localKey = null,
        public ?string $label = null
    ) {
    }
}
