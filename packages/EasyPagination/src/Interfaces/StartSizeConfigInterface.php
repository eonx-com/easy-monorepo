<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
interface StartSizeConfigInterface
{
    public function getSizeAttribute(): string;

    public function getSizeDefault(): int;

    public function getStartAttribute(): string;

    public function getStartDefault(): int;
}
