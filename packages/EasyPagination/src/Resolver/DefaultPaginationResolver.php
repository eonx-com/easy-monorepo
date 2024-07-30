<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Resolver;

use EonX\EasyPagination\ValueObject\Pagination;
use EonX\EasyPagination\ValueObject\PaginationConfigInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;

final readonly class DefaultPaginationResolver
{
    public function __construct(
        private PaginationConfigInterface $config,
    ) {
        // No body needed
    }

    public function __invoke(): PaginationInterface
    {
        return Pagination::create(
            $this->config->getPageDefault(),
            $this->config->getPerPageDefault(),
            $this->config->getPageAttribute(),
            $this->config->getPerPageAttribute()
        );
    }
}
