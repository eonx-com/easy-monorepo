<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Repository;

use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorInterface;
}
