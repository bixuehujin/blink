<?php

declare(strict_types=1);

namespace blink\database\schema;

interface ColumnContract
{
    public function getName(): string;
    public function isNullable(): bool;
    public function getType(): string;
    public function getLength(): ?int;
    public function getDefault(): mixed;
    public function isPrimary(): bool;
    public function getPrecision(): ?int;
    public function getScale(): ?int;
    public function isUnsigned(): bool;
    public function getComment(): string;
}
