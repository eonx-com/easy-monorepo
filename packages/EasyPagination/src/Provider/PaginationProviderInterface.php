<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

use EonX\EasyPagination\ValueObject\Pagination;

interface PaginationProviderInterface
{
    public function getPagination(): Pagination;

    public function getPaginationConfig(): PaginationConfigProviderInterface;

    public function setResolver(callable $resolver): self;
}
