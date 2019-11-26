<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface LengthAwarePaginatorInterface
{
    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrentPage(): int;

    /**
     * Get current items being paginated.
     *
     * @return mixed[]
     */
    public function getItems(): array;

    /**
     * Get items to be shown per page.
     *
     * @return int
     */
    public function getItemsPerPage(): int;

    /**
     * Get total number of paginated items.
     *
     * @return int
     */
    public function getTotalItems(): int;

    /**
     * Get total number of pages based on the total number of items.
     *
     * @return int
     */
    public function getTotalPages(): int;

    /**
     * When current page has a next page.
     *
     * @return bool
     */
    public function hasNextPage(): bool;

    /**
     * When current page has a previous page.
     *
     * @return bool
     */
    public function hasPreviousPage(): bool;
}


