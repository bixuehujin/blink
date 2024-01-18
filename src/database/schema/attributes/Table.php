<?php

declare(strict_types=1);

namespace blink\database\schema\attributes;

use Attribute;

#[Attribute]
class Table
{
    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public ?string $comment = null,
    ) {
    }
}
