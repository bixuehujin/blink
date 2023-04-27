<?php

declare(strict_types=1);

namespace blink\database\expr;

use blink\expression\expr\Expr;
use function blink\expression\has;


class HasExpr extends Expr
{
    public string $relation;
    public Expr $filter;

    public function __construct(string $relation, Expr $filter)
    {
        $this->relation = $relation;
        $this->filter = $filter;
    }

    public static function type(): string
    {
        return 'has';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'relation' => $this->relation,
            'filter' => $this->filter->toArray(),
        ];
    }
}
