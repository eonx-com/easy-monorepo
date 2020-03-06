<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface StartSizeConfigInterface
{
    public function getSizeAttribute(): string;

    public function getSizeDefault(): int;

    public function getStartAttribute(): string;

    public function getStartDefault(): int;
}
