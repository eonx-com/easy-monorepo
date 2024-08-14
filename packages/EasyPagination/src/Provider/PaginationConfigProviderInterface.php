<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

interface PaginationConfigProviderInterface
{
    public function getPageAttribute(): string;

    public function getPageDefault(): int;

    public function getPerPageAttribute(): string;

    public function getPerPageDefault(): int;
}
