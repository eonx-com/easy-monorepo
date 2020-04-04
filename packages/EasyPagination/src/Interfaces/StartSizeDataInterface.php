<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface StartSizeDataInterface
{
    public function getSize(): int;

    public function getSizeAttribute(): string;

    public function getStart(): int;

    public function getStartAttribute(): string;

    public function getUrl(): string;
}
