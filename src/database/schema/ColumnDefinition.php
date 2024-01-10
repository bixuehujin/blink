<?php

namespace blink\database\schema;

class ColumnDefinition implements ColumnContract
{
    public string $name;
    public string $type;
    public bool $nullable = false;
    public ?int $length = null;
    public mixed $default = null;
    public bool $autoIncrement = false;
    public bool $primaryKey = false;
    public ?int $precision = null;
    public ?int $scale = null;
    public bool $unsigned = false;
    public string $comment = '';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function integer(bool $unsigned = false): self
    {
        $this->type = 'int32';
        $this->unsigned = $unsigned;
        return $this;
    }

    public function tinyInteger(bool $unsigned = false): self
    {
        $this->type = 'int8';
        $this->unsigned = $unsigned;
        return $this;
    }

    public function smallInteger(bool $unsigned = false): self
    {
        $this->type = 'int16';
        $this->unsigned = $unsigned;
        return $this;
    }

    public function mediumInteger(bool $unsigned = false): self
    {
        $this->type = 'int24';
        $this->unsigned = $unsigned;
        return $this;
    }

    public function bigInteger(bool $unsigned = false): self
    {
        $this->type = 'int64';
        $this->unsigned = $unsigned;
        return $this;
    }

    public function string(int $length = 255): self
    {
        $this->type = 'string';
        $this->length = $length;
        return $this;
    }

    public function text(): self
    {
        $this->type = 'text';
        return $this;
    }

    public function mediumText(): self
    {
        $this->type = 'mediumText';
        return $this;
    }

    public function longText(): self
    {
        $this->type = 'longText';
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;
        return $this;
    }

    public function primary($autoIncrement = true): self
    {
        $this->primaryKey = true;
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function isPrimary(): bool
    {
        return $this->primaryKey;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
