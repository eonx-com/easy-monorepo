<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface PaginationProviderInterface
{
    public function getPagination(): PaginationInterface;

    public function getPaginationConfig(): PaginationConfigInterface;

    public function setResolver(callable $resolver): self;
}
