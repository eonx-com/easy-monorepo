<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

use EonX\EasyPagination\Pagination\PaginationInterface;

interface PaginationProviderInterface
{
    public function getPagination(): PaginationInterface;

    public function getPaginationConfigProvider(): PaginationConfigProviderInterface;

    public function setResolver(callable $resolver): self;
}
