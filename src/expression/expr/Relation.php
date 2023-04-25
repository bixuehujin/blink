<?php

declare(strict_types=1);

namespace blink\expression\expr;

class Relation extends Expr
{
    /**
     * The name of the relation.
     *
     * @var string
     */
    public string $name;
    /**
     * The columns to be selected from the relation, defaults to all columns.
     *
     * @var Expr[]
     */
    public array $columns = [];

    public function __construct(string $name, array $columns = [])
    {
        $this->name = $name;
        $this->columns = $columns;
    }

    public static function type(): string
    {
        return 'relation';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'name' => $this->name,
            'columns' => array_map(fn (Expr $column) => $column->toArray(), $this->columns),
        ];
    }
}
