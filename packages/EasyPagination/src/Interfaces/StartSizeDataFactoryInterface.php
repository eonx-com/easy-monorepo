<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
interface StartSizeDataFactoryInterface
{
    public function create(
        ?int $start = null,
        ?int $size = null,
        ?string $startAttr = null,
        ?string $sizeAttr = null,
        ?string $url = null
    ): StartSizeDataInterface;
}
