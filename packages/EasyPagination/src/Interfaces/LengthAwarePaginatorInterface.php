<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

/**
 * @deprecated since 3.2, will be removed in 4.0. Will be replaced by new implementation.
 */
interface LengthAwarePaginatorInterface
{
    public function getCurrentPage(): int;

    /**
     * @return mixed[]
     */
    public function getItems(): array;

    public function getItemsPerPage(): int;

    public function getNextPageUrl(): ?string;

    public function getPreviousPageUrl(): ?string;

    public function getTotalItems(): int;

    public function getTotalPages(): int;

    public function hasNextPage(): bool;

    public function hasPreviousPage(): bool;
}
