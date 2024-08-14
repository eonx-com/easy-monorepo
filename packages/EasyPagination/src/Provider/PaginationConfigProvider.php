<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

final readonly class PaginationConfigProvider implements PaginationConfigProviderInterface
{
    public function __construct(
        private string $pageAttribute,
        private int $pageDefault,
        private string $perPageAttribute,
        private int $perPageDefault,
    ) {
    }

    public function getPageAttribute(): string
    {
        return $this->pageAttribute;
    }

    public function getPageDefault(): int
    {
        return $this->pageDefault;
    }

    public function getPerPageAttribute(): string
    {
        return $this->perPageAttribute;
    }

    public function getPerPageDefault(): int
    {
        return $this->perPageDefault;
    }
}
