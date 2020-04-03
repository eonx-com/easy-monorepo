<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

final class ArrayPaginator extends AbstractLengthAwarePaginator
{
    /**
     * @var mixed[]
     */
    private $items;

    /**
     * @var int
     */
    private $total;

    /**
     * @param mixed[] $items
     */
    public function __construct(array $items, int $total, StartSizeDataInterface $startSizeData, ?string $path = null)
    {
        $this->items = $items;
        $this->total = $total;

        parent::__construct($startSizeData, $path);
    }

    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalItems(): int
    {
        return $this->total;
    }
}
