<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers;

use EonX\EasyPagination\Interfaces\PaginationConfigInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;

final class DefaultPaginationResolver
{
    public function __construct(
        private PaginationConfigInterface $config,
    ) {
        // No body needed.
    }

    public function __invoke(): PaginationInterface
    {
        return Pagination::create(
            $this->config->getPageDefault(),
            $this->config->getPerPageDefault(),
            $this->config->getPageAttribute(),
            $this->config->getPerPageAttribute(),
        );
    }
}
