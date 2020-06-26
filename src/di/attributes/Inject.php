<?php

declare(strict_types=1);

namespace blink\di\attributes;

use PhpAttribute;

<<PhpAttribute>>
class Inject
{
    public function __construct(protected ?string $reference = null)
    {
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }
}
