<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Data;

use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

final class StartSizeData implements StartSizeDataInterface
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $start;

    public function __construct(int $start, int $size)
    {
        $this->start = $start;
        $this->size = $size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getStart(): int
    {
        return $this->start;
    }
}
