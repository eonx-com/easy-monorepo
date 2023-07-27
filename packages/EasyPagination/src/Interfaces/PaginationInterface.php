<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface PaginationInterface
{
    public const DEFAULT_PAGE = 1;

    public const DEFAULT_PAGE_ATTRIBUTE = 'page';

    public const DEFAULT_PER_PAGE = 15;

    public const DEFAULT_PER_PAGE_ATTRIBUTE = 'perPage';

    public const DEFAULT_URL = '/';

    public function getPage(): int;

    public function getPageAttribute(): string;

    public function getPerPage(): int;

    public function getPerPageAttribute(): string;

    public function getUrl(int $page): string;

    public function setUrlResolver(?callable $urlResolver = null): self;
}
