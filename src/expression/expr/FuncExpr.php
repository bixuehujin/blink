<?php

namespace blink\expression\expr;

class FuncExpr extends Expr
{
    public string $name;
    public array $args;

    public function __construct(string $name, array $args)
    {
        $this->name = $name;
        $this->args = $args;
    }

    public static function type(): string
    {
        return 'func';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'name' => $this->name,
            'args' => $this->args,
        ];
    }
}
