<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

interface LengthAwarePaginatorInterface extends PaginatorInterface
{
    public function getFirstPageUrl(): string;

    public function getLastPageUrl(): string;

    public function getTotalItems(): int;

    public function getTotalPages(): int;

    public function hasNextPage(): bool;

    public function hasPreviousPage(): bool;

    public function isLargeDatasetEnabled(): bool;

    public function setLargeDatasetEnabled(?bool $largeDatasetEnabled = null): self;
}
