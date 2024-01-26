<?php

namespace blink\database\schema\attributes;

abstract class Relationship
{
    public function __construct(
        public string $target,
        public ?string $name = null,
        public ?string $foreignKey = null,
        public ?string $localKey = null,
        public ?string $label = null,
    ) {
    }
}
