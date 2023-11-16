<?php

namespace blink\database;

interface CompilerContract
{
    public function setContext(Context $context);
    public function renderOne(Query $query): array|object|null;
    public function renderAll(Query $query): Collection;
    public function renderPaginate(Query $query, int $page = 1, int $perPage = 20): Collection;
    public function insertAll(Query $query, array $records): array;
    public function updateAll(Query $query, array $records): array;
    public function delete(Query $query, array $options): int;
}
