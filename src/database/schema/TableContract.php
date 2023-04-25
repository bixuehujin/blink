<?php

declare(strict_types=1);

namespace blink\database\schema;

interface TableContract
{
    public function getDriver(): string;

    public function getName(): string;

    public function hasColumn(string $name): bool;

    public function getColumn(string $name): ColumnContract;

    /**
     * @return ColumnContract[]
     */
    public function getColumns(): array;

    public function getRelation(string $name): RelationContract;

    /**
     * @return RelationContract[]
     */
    public function getRelations(): array;
}
