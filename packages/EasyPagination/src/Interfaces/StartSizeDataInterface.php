<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface StartSizeDataInterface
{
    public function getStart(): int;

    public function getSize(): int;
}
