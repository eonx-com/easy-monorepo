<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface PaginationInterface
{
    /**
     * @var string
     */
    public const DEFAULT_PAGE_ATTRIBUTE = 'page';

    /**
     * @var string
     */
    public const DEFAULT_PER_PAGE_ATTRIBUTE = 'perPage';

    /**
     * @var string
     */
    public const DEFAULT_URL = '/';

    public function getPage(): int;

    public function getPageAttribute(): string;

    public function getPerPage(): int;

    public function getPerPageAttribute(): string;

    public function getUrl(): string;
}
