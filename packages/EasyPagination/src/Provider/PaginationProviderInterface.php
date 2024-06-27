<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

use EonX\EasyPagination\ValueObject\PaginationConfigInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;

interface PaginationProviderInterface
{
    public function getPagination(): PaginationInterface;

    public function getPaginationConfig(): PaginationConfigInterface;

    public function setResolver(callable $resolver): self;
}
