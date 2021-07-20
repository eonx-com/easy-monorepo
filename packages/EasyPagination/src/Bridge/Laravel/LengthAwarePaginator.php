<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
final class LengthAwarePaginator implements LengthAwarePaginatorInterface
{
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private $illuminatePaginator;

    public function __construct(LengthAwarePaginatorContract $illuminatePaginator)
    {
        $this->illuminatePaginator = $illuminatePaginator;
    }

    public function getCurrentPage(): int
    {
        return $this->illuminatePaginator->currentPage();
    }

    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        return $this->illuminatePaginator->items();
    }

    public function getItemsPerPage(): int
    {
        return $this->illuminatePaginator->perPage();
    }

    public function getNextPageUrl(): ?string
    {
        return $this->illuminatePaginator->nextPageUrl();
    }

    public function getPreviousPageUrl(): ?string
    {
        return $this->illuminatePaginator->previousPageUrl();
    }

    public function getTotalItems(): int
    {
        return $this->illuminatePaginator->total();
    }

    public function getTotalPages(): int
    {
        return $this->illuminatePaginator->lastPage();
    }

    public function hasNextPage(): bool
    {
        return (bool)$this->illuminatePaginator->nextPageUrl();
    }

    public function hasPreviousPage(): bool
    {
        return (bool)$this->illuminatePaginator->previousPageUrl();
    }
}
