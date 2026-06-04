<?php

namespace blink\expression\expr;

class Literal extends Expr
{
    public mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public static function type(): string
    {
        return 'literal';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'value' => $this->value,
        ];
    }

    public static function fromArray(array $data): static
    {
        $expr = new static($data['value']);
        return $expr->withCommonFields($data);
    }
}
