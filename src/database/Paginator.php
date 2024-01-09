<?php

declare(strict_types=1);

namespace blink\database;


class Paginator
{
    protected int $total;
    protected int $currentPage;
    protected int $totalPages;
    protected int $perPage;

    public function __construct(int $total, int $currentPage, int $totalPages, int $perPage)
    {
        $this->total = $total;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->perPage = $perPage;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function totalPages(): int
    {
        return $this->totalPages;
    }

    public function total(): int
    {
        return $this->total;
    }
}
