<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

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
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     */
    public function __construct(StartSizeDataInterface $startSizeData)
    {
        $this->start = $startSizeData->getStart();
        $this->size = $startSizeData->getSize();
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
