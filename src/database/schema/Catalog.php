<?php

namespace blink\database\schema;

class Catalog
{
    protected array $tables = [];

    protected array $resolvers = [];

    public function registerResolver(callable $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    public function addTable(TableContract $table): void
    {
        $tableName = $table->getName();

        if (isset($this->tables[$tableName])) {
            throw new \RuntimeException("Table $tableName already exists.");
        }

        $this->tables[$table->getName()] = $table;
    }

    protected function resolveTable(string $tableName): ?TableContract
    {
        foreach ($this->resolvers as $resolver) {
            if ($table = $resolver($tableName)) {
                return $table;
            }
        }
        return null;
    }

    public function find(string $tableName): TableContract
    {
        if (!isset($this->tables[$tableName])) {
            $table = $this->resolveTable($tableName);
            if (! $table) {
                throw new \RuntimeException("Table $tableName not found.");
            }

            $this->tables[$tableName] = $table;
        }

        return $this->tables[$tableName];
    }
}
