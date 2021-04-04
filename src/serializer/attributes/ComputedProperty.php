<?php


namespace blink\serializer\attributes;

use Attribute;

#[Attribute]
class ComputedProperty
{
    public function __construct(
        public string $name,
        public string $setter = '',
        public string $getter = '',
    ) {
    }
}
