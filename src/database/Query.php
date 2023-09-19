<?php

namespace blink\database;

use blink\expression\expr\AndExpr;
use blink\expression\expr\Expr;
use blink\expression\expr\OrExpr;
use blink\expression\expr\Relation;
use function blink\expression\and_;
use function blink\expression\binary;
use function blink\expression\lit;
use function blink\expression\or_;
use function blink\expression\rel;

/**
 * @template T
 */
class Query
{
    protected Context $context;

    protected string $from;
    /**
     * @var Expr[]
     */
    protected array $columns = [];
    /**
     * @var Expr[]
     */
    protected array $groups = [];
    /**
     * @var Relation[]
     */
    protected array $relations = [];
    /**
     * @var array{string, 'desc' | 'asc'}
     */
    protected array $orders = [];
    protected ?Expr $where  = null;
    protected ?string $intoClass = null;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function from(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function select(string|Expr ...$columns): self
    {
        $this->columns = array_map(fn ($column) => Expr::normalize($column), $columns);
        return $this;
    }

    public function addSelect(string|Expr ...$columns): self
    {
        $this->columns = [
            ...$this->columns,
            ...array_map(fn ($column) => Expr::normalize($column), $columns)
        ];

        return $this;
    }

    public function with(Relation|string ...$relations): self
    {
        $this->relations = [
            ...$this->relations,
            ...array_map(fn ($relation) => is_string($relation) ? rel($relation) : $relation, $relations)
        ];

        return $this;
    }

    public function has(string $relation,  Expr $filter): self
    {
        $this->filter(rel($relation)->has($filter));

        return $this;
    }

    public function groupBy(string|Expr ... $columns): self
    {
        $this->groups = array_map(fn ($column) => Expr::normalize($column), $columns);
        return $this;
    }

    /**
     * @param Expr|string $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @return $this
     */
    public function where(string|Expr $column, $operator = null, mixed $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '==';
        }

        return $this->whereInternal($column, $operator, $value);
    }

    /**
     * @param Expr|string $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @return $this
     */
    public function orWhere(string|Expr $column, $operator = null, mixed $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '==';
        }

        return $this->whereInternal($column, $operator, $value, true);
    }

    protected function whereInternal(string|Expr $column, string $operator, mixed $value, bool $usingOr = false): self
    {
        $column = Expr::normalize($column);
        $value = $value instanceof Expr ? $value : lit($value);

        if (is_null($this->where)) {
            $this->where = binary($column, $operator, $value);
        } else if ($usingOr) {
            $this->where = or_(
                $this->where,
                binary($column, $operator, $value)
            );
        } else {
            $this->where = and_(
                $this->where,
                binary($column, $operator, $value)
            );
        }

        return $this;
    }

    public function whereIn(string|Expr $column, array $values, bool $isAnd = true): self
    {
        if ($isAnd) {
            $this->where($column, 'in', $values);
        } else {
            $this->orWhere($column, 'in', $values);
        }

        return $this;
    }

    public function orWhereIn(string|Expr $column, array $values): self
    {
        return $this->whereIn($column, $values, false);
    }


    public function whereNotIn(string|Expr $column, array $values, bool $isAnd = true): self
    {
        if ($isAnd) {
            $this->where($column, 'not in', $values);
        } else {
            $this->orWhere($column, 'not in', $values);
        }

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = [$column, $direction];

        return $this;
    }

    public function orWhereNotIn(string|Expr $column, array $values): self
    {
        return $this->whereNotIn($column, $values, false);
    }

    public function filter(Expr $expr): self
    {
        if (is_null($this->where)) {
            $this->where = $expr;
        } else if ($this->where instanceof AndExpr) {
            $this->where->add($expr);
        } else {
            $this->where = and_($this->where, $expr);
        }

        return $this;
    }

    public function orFilter(Expr $expr): self
    {
        if (is_null($this->where)) {
            $this->where = $expr;
        } else if ($this->where instanceof OrExpr) {
            $this->where->add($expr);
        } else {
            $this->where = or_($this->where, $expr);
        }

        return $this;
    }

    public function into(string $entityClass): self
    {
        $this->intoClass = $entityClass;

        return $this;
    }

    /**
     * @return T|null
     */
    public function first(): mixed
    {
        return $this->context->queryOne($this);
    }

    /**
     * @return Collection<T>
     */
    public function all(): Collection
    {
        return $this->context->queryAll($this);
    }

    /**
     * @return Collection<T>
     */
    public function paginate(int $page = 1, int $perPage = 20): Collection
    {
        return $this->context->paginate($this, $page, $perPage);
    }

    public function getWhere(): ?Expr
    {
        return $this->where;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return Expr[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return Expr[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @return Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getEntityClass(): ?string
    {
        return $this->intoClass;
    }
}
