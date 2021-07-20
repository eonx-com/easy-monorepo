<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

/**
 * @deprecated since 3.2, will be removed in 4.0. Use PaginationInterface instead.
 */
interface StartSizeDataInterface
{
    public function getSize(): int;

    public function getSizeAttribute(): string;

    public function getStart(): int;

    public function getStartAttribute(): string;

    public function getUrl(): string;
}
