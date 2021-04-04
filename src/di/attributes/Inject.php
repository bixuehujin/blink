<?php

declare(strict_types=1);

namespace blink\di\attributes;

use Attribute;

#[Attribute]
class Inject
{
    public function __construct(
        protected ?string $reference = null,
        protected ?string $setter = null
    )
    {
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getSetter(): ?string
    {
        return $this->setter;
    }
}
