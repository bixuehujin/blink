<?php

namespace blink\expression\expr;

class Variable extends Expr
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function type(): string
    {
        return 'variable';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'name' => $this->name,
        ];
    }
}
