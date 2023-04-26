<?php

namespace blink\expression\expr;

abstract class Expr
{
    public ?string $alias = null;
    public bool $disabled = false;

    abstract public static function type(): string;

    public static function normalize(string|Expr $expr): Expr
    {
        if (is_string($expr)) {
            return new Column($expr);
        }

        return $expr;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function eq(mixed $value): self
    {
        return new BinaryExpr($this, '==', $value);
    }

    public function neq(mixed $value): self
    {
        return new BinaryExpr($this, '!=', $value);
    }

    public function gt(mixed $value): self
    {
        return new BinaryExpr($this, '>', $value);
    }

    public function gte(mixed $value): self
    {
        return new BinaryExpr($this, '>=', $value);
    }

    public function lt(mixed $value): self
    {
        return new BinaryExpr($this, '<', $value);
    }

    public function lte(mixed $value): self
    {
        return new BinaryExpr($this, '<=', $value);
    }

    public function xor(mixed $value): self
    {
        return new BinaryExpr($this, 'xor', $value);
    }

    public function plus(mixed $value): self
    {
        return new BinaryExpr($this, '+', $value);
    }

    public function minus(mixed $value): self
    {
        return new BinaryExpr($this, '-', $value);
    }

    public function multiply(mixed $value): self
    {
        return new BinaryExpr($this, '*', $value);
    }

    public function divide(mixed $value): self
    {
        return new BinaryExpr($this, '/', $value);
    }

    public function mod(mixed $value): self
    {
        return new BinaryExpr($this, '%', $value);
    }

    public function in(mixed $value): self
    {
        return new BinaryExpr($this, 'in', $value);
    }

    public function notIn(mixed $value): self
    {
        return new BinaryExpr($this, 'not in', $value);
    }

    public function contains(mixed $value): self
    {
        return new BinaryExpr($this, 'contains', $value);
    }

    public function notContains(mixed $value): self
    {
        return new BinaryExpr($this, 'not contains', $value);
    }

    public function startsWith(mixed $value): self
    {
        return new BinaryExpr($this, 'starts with', $value);
    }

    public function endsWith(mixed $value): self
    {
        return new BinaryExpr($this, 'ends with', $value);
    }

    public function overlaps(mixed $value): self
    {
        return new BinaryExpr($this, 'overlaps', $value);
    }

    public function notOverlaps(mixed $value): self
    {
        return new BinaryExpr($this, 'not overlaps', $value);
    }

    public function as(string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    public function disabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => static::type(),
            'alias' => $this->alias,
            'disabled' => $this->disabled,
        ]);
    }
}
