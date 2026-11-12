<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

interface PaginationConfigProviderInterface
{
    public function getPageAttribute(): string;

    public function getPageDefault(): int;

    public function getPerPageAttribute(): string;

    public function getPerPageDefault(): int;

    /**
     * The largest page size a client can request, or null for no limit
     */
    public function getPerPageMax(): ?int;
}
