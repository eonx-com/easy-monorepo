<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

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
     * ArrayPaginator constructor.
     *
     * @param mixed[] $items
     * @param int $total
     * @param int $start
     * @param int $size
     */
    public function __construct(array $items, int $total, int $start, int $size)
    {
        $this->items = $items;
        $this->total = $total;

        parent::__construct($start, $size);
    }

    /**
     * Get current items being paginated.
     *
     * @return mixed[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get total number of paginated items.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->total;
    }
}
