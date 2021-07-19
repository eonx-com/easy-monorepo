<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface LengthAwarePaginatorNewInterface extends PaginatorInterface
{
    public function getTotalItems(): int;

    public function getTotalPages(): int;

    public function hasNextPage(): bool;

    public function hasPreviousPage(): bool;
}
