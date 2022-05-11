<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class IterableLengthAwarePaginator extends AbstractLengthAwarePaginator
{
    /**
     * @param iterable<mixed> $iterable
     */
    public function __construct(
        PaginationInterface $pagination,
        private readonly iterable $iterable,
        private readonly int $total
    ) {
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
