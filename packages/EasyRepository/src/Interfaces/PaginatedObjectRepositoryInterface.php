<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Interfaces;

use LoyaltyCorp\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    /**
     * Return a paginated list of objects managed by the repository.
     *
     * @param null|\LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \LoyaltyCorp\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface;
}

\class_alias(
    PaginatedObjectRepositoryInterface::class,
    'StepTheFkUp\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface',
    false
);
