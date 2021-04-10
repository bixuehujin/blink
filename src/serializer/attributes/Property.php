<?php

declare(strict_types=1);

namespace blink\serializer\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Property
{
    public string $name;
    public bool $guarded;
    public string|bool $getter;
    public string|bool $setter;

    public bool $hasDefault = false;
    public mixed $defaultValue;

    public function __construct(string|bool $getter = '', string|bool $setter = '')
    {
        $this->setter = $setter;
        $this->getter = $getter;
    }

    public function withGuarded(bool $guarded): static
    {
        $this->guarded = $guarded;

        return $this;
    }

    public function withName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function withDefaultValue(mixed $value): static
    {
        $this->hasDefault = true;
        $this->defaultValue = $value;

        return $this;
    }
}
