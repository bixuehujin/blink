<?php

namespace blink\expression\expr;

class AggExpr extends Expr
{
    public string $method;
    public Expr   $expr;
    public array  $options;

    public function __construct(string $method, Expr $expr, array $options = [])
    {
        $this->method = $method;
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
            'method' => $this->method,
            'expr' => $this->expr->toArray(),
            'options' => $this->options,
        ];
    }
}
