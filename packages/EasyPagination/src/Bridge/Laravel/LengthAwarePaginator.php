<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Bridge\Laravel;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use LoyaltyCorp\EasyPagination\Interfaces\LengthAwarePaginatorInterface;

final class LengthAwarePaginator implements LengthAwarePaginatorInterface
{
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private $illuminatePaginator;

    /**
     * LengthAwarePaginator constructor.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $illuminatePaginator
     */
    public function __construct(LengthAwarePaginatorContract $illuminatePaginator)
    {
        $this->illuminatePaginator = $illuminatePaginator;
    }

    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->illuminatePaginator->currentPage();
    }

    /**
     * Get current items being paginated.
     *
     * @return mixed[]
     */
    public function getItems(): array
    {
        return $this->illuminatePaginator->items();
    }

    /**
     * Get items to be shown per page.
     *
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->illuminatePaginator->perPage();
    }

    /**
     * Get total number of paginated items.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->illuminatePaginator->total();
    }

    /**
     * Get total number of pages based on the total number of items.
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->illuminatePaginator->lastPage();
    }

    /**
     * When current page has a next page.
     *
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return (bool)$this->illuminatePaginator->nextPageUrl();
    }

    /**
     * When current page has a previous page.
     *
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return (bool)$this->illuminatePaginator->previousPageUrl();
    }
}

\class_alias(
    LengthAwarePaginator::class,
    'StepTheFkUp\EasyPagination\Bridge\Laravel\LengthAwarePaginator',
    false
);
