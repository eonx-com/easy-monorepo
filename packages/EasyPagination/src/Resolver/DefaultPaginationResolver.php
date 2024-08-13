<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Resolver;

use EonX\EasyPagination\Pagination\Pagination;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Provider\PaginationConfigProviderInterface;

final readonly class DefaultPaginationResolver
{
    public function __construct(
        private PaginationConfigProviderInterface $paginationConfigProvider,
    ) {
        // No body needed
    }

    public function __invoke(): PaginationInterface
    {
        return Pagination::create(
            $this->paginationConfigProvider->getPageDefault(),
            $this->paginationConfigProvider->getPerPageDefault(),
            $this->paginationConfigProvider->getPageAttribute(),
            $this->paginationConfigProvider->getPerPageAttribute()
        );
    }
}
