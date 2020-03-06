<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface LengthAwarePaginatorInterface
{
    public function getCurrentPage(): int;

    /**
     * @return mixed[]
     */
    public function getItems(): array;

    public function getItemsPerPage(): int;

    public function getTotalItems(): int;

    public function getTotalPages(): int;

    public function hasNextPage(): bool;

    public function hasPreviousPage(): bool;
}
