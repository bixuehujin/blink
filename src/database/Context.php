<?php

declare(strict_types=1);

namespace blink\database;

use blink\database\schema\Catalog;

class Context
{
    public Catalog $catalog;
    /**
     * @var array
     */
    protected array $compilers = [];

    public function __construct()
    {
        $this->catalog = new Catalog();
    }

    public function registerResolver(callable $resolver): void
    {
        $this->catalog->registerResolver($resolver);
    }

    public function registerCompiler(string $driver,  CompilerContract $compiler): void
    {
        if (isset($this->compilers[$driver])) {
            throw new \RuntimeException("The driver's compiler is already registered.");
        }

        $compiler->setContent($this);

        $this->compilers[$driver] = $compiler;
    }

    public function getCompiler(string $driver): CompilerContract
    {
        if (!isset($this->compilers[$driver])) {
            throw new \RuntimeException("The driver's compiler is not registered.");
        }

        return $this->compilers[$driver];
    }

    public function queryOne(Query $query): array|object|null
    {
        $table = $this->catalog->find($query->getFrom());

        $compiler = $this->getCompiler($table->getDriver());

        return $compiler->renderOne($query);
    }

    public function queryAll(Query $query): Collection
    {
        $table = $this->catalog->find($query->getFrom());

        $compiler = $this->getCompiler($table->getDriver());

        return $compiler->renderAll($query);
    }

    public function paginate(Query $query, int $page = 1, int $perPage = 20): Collection
    {
        $table = $this->catalog->find($query->getFrom());

        $compiler = $this->getCompiler($table->getDriver());

        return $compiler->renderPaginate($query, $page, $perPage);
    }

    public function newQuery(): Query
    {
        return new Query($this);
    }
}
