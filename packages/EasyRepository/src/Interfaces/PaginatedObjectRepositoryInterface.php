<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorInterface;
}
