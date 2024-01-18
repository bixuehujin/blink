<?php

declare(strict_types=1);

namespace blink\database\schema\attributes;

use Attribute;

#[Attribute]
class PrimaryKey
{
    public function __construct(
        public ?bool $autoIncrement = null,
    ) {
    }
}
