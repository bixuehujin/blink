<?php

declare(strict_types=1);

namespace blink\database;

use ArrayAccess;
use IteratorAggregate;
use JsonSerializable;
use Countable;
use ArrayIterator;


class Collection implements ArrayAccess, IteratorAggregate, JsonSerializable, Countable
{
    protected array $items;
    protected ?Paginator $paginator;


    public function __construct(array $items, ?Paginator $paginator = null)
    {
        $this->items = $items;
        $this->paginator = $paginator;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function paginated(): bool
    {
        return $this->paginator !== null;
    }

    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): mixed
    {
        return reset($this->items);
    }
}
