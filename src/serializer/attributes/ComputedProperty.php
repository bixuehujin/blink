<?php


namespace blink\serializer\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ComputedProperty
{
    public function __construct(
        public string $name,
        public string $setter = '',
        public string $getter = '',
    ) {
    }
}
