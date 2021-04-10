<?php

declare(strict_types=1);

namespace blink\serializer\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class In
{
    public function __construct(public string $where)
    {
    }
}
