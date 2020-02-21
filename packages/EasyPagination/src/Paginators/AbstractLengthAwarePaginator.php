<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;

abstract class AbstractLengthAwarePaginator implements LengthAwarePaginatorInterface
{
    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $start;

    /**
     * EmptyPaginator constructor.
     *
     * @param int $start
     * @param int $size
     */
    public function __construct(int $start, int $size)
    {
        $this->start = $start;
        $this->size = $size;
    }

    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->start;
    }

    /**
     * Get items to be shown per page.
     *
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->size;
    }

    /**
     * Get total number of pages based on the total number of items.
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return \max((int)\ceil($this->getTotalItems() / $this->getItemsPerPage()), 1);
    }

    /**
     * When current page has a next page.
     *
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->getTotalPages() > $this->getCurrentPage();
    }

    /**
     * When current page has a previous page.
     *
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->getCurrentPage() > 1;
    }
}
