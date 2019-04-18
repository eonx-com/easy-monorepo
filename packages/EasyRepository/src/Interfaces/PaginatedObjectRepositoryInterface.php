<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Interfaces;

use StepTheFkUp\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    /**
     * Return a paginated list of objects managed by the repository.
     *
     * @param null|\StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \StepTheFkUp\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface;
}

\class_alias(
    PaginatedObjectRepositoryInterface::class,
    'LoyaltyCorp\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface',
    false
);
