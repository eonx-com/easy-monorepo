<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Interfaces;

interface PagePaginationDataInterface
{
    /**
     * Get page number.
     *
     * @return int
     */
    public function getPageNumber(): int;

    /**
     * Get number of items per page.
     *
     * @return int
     */
    public function getPageSize(): int;
}