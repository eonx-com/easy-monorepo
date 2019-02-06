<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Data;

use StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface;

final class PagePaginationData implements PagePaginationDataInterface
{
    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * PagePaginationData constructor.
     *
     * @param int $pageNumber
     * @param int $pageSize
     */
    public function __construct(int $pageNumber, int $pageSize)
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;
    }

    /**
     * Get page number.
     *
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * Get number of items per page.
     *
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}