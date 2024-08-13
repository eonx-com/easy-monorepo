<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Repository;

use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorInterface;
}
