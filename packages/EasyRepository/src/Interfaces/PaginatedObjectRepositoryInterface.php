<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface;
}
