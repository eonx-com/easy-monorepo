<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Pagination;

interface PaginationInterface
{
    public function getPage(): int;

    public function getPageAttribute(): string;

    public function getPerPage(): int;

    public function getPerPageAttribute(): string;

    public function getUrl(int $page): string;

    public function setUrlResolver(?callable $urlResolver = null): self;
}
