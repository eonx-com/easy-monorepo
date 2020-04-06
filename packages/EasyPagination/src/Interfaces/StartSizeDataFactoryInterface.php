<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

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
