<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorNewInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorNewInterface;
}
