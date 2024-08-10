<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Repository;

use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyPagination\ValueObject\Pagination;

interface PaginatedObjectRepositoryInterface extends ObjectRepositoryInterface
{
    public function paginate(?Pagination $pagination = null): LengthAwarePaginatorInterface;
}
