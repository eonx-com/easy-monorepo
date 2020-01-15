<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    /**
     * Return a paginated list of objects managed by the repository.
     *
     * @param null|\EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface;
}
