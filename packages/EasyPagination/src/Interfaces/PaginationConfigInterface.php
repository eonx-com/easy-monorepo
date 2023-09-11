<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface PaginationConfigInterface
{
    public function getPageAttribute(): string;

    public function getPageDefault(): int;

    public function getPerPageAttribute(): string;

    public function getPerPageDefault(): int;
}
