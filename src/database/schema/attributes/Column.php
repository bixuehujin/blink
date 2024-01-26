<?php

declare(strict_types=1);

namespace blink\database\schema\attributes;

use Attribute;
use blink\typing\Type;

#[Attribute]
class Column
{
    public function __construct(
        public ?string $name = null,
        public ?Type $type = null,
        public mixed $default = null,
        public ?string $label = null,
        public ?string $comment = null,
    ) {
    }
}
