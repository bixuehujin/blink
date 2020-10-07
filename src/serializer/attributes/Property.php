<?php

declare(strict_types=1);

namespace blink\serializer\attributes;

use Attribute;

#[Attribute]
class Property
{
    public bool $guarded;
    public string $getter;
    public string $setter;

    public function __construct(bool $guarded = false, string $getter = '', string $setter = '')
    {
        $this->guarded = $guarded || $getter || $setter;
        $this->setter = $setter;
        $this->getter = $getter;
    }
}
