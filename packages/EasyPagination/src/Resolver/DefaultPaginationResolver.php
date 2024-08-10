<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Resolver;

use EonX\EasyPagination\Provider\PaginationConfigProviderInterface;
use EonX\EasyPagination\ValueObject\Pagination;

final readonly class DefaultPaginationResolver
{
    public function __construct(
        private PaginationConfigProviderInterface $config,
    ) {
        // No body needed
    }

    public function __invoke(): Pagination
    {
        return Pagination::create(
            $this->config->getPageDefault(),
            $this->config->getPerPageDefault(),
            $this->config->getPageAttribute(),
            $this->config->getPerPageAttribute()
        );
    }
}
