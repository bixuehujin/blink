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

    /**
     * The filter to be applied to the relation.
     *
     * @var Expr|null
     */
    public ?Expr $filter = null;

    public function __construct(string $name, array $columns = [], ?Expr $filter = null)
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->filter = $filter;
    }

    public static function type(): string
    {
        return 'relation';
    }

    public function has(Expr $expr): HasExpr
    {
        return new HasExpr($this->name, $expr);
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'name' => $this->name,
            'columns' => array_map(fn (Expr $column) => $column->toArray(), $this->columns),
            'filter' => $this->filter ? $this->filter->toArray() : null,
        ];
    }

    public static function fromArray(array $data): static
    {
        $columns = array_map(fn (array $c) => Expr::fromArray($c), $data['columns'] ?? []);
        $filter = isset($data['filter']) ? Expr::fromArray($data['filter']) : null;
        $expr = new static($data['name'], $columns, $filter);
        return $expr->withCommonFields($data);
    }
}
