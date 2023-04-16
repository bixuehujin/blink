<?php

namespace blink\expression\expr;

class AggExpr extends Expr
{
    public string $name;
    public Expr   $expr;
    public array  $options;

    public function __construct(string $name, string|Expr $expr, array $options = [])
    {
        $this->name = $name;
        $this->expr = self::normalize($expr);
        $this->options = $options;
    }

    public static function type(): string
    {
        return 'agg';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'name' => $this->name,
            'expr' => $this->expr,
        ];
    }
}
