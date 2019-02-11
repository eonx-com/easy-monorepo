<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Interfaces;

use StepTheFkUp\Pagination\Interfaces\LengthAwarePaginatorInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    /**
     * Return a paginated list of objects managed by the repository.
     *
     * @param null|\StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \StepTheFkUp\Pagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface;
}
