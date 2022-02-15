<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyUtils\CollectorHelper;

final class IterableLengthAwarePaginator extends AbstractLengthAwarePaginator
{
    /**
     * @var iterable<mixed>
     */
    private $iterable;

    /**
     * @var int
     */
    private $total;

    /**
     * @param iterable<mixed> $iterable
     */
    public function __construct(PaginationInterface $pagination, iterable $iterable, int $total)
    {
        $this->iterable = $iterable;
        $this->total = $total;

        parent::__construct($pagination);
    }

    public function getTotalItems(): int
    {
        return $this->total;
    }

    /**
     * @return mixed[]
     */
    protected function doGetItems(): array
    {
        return CollectorHelper::convertToArray($this->iterable);
    }
}
