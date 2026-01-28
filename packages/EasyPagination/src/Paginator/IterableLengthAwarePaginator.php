<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use EonX\EasyPagination\Pagination\PaginationInterface;

final class IterableLengthAwarePaginator extends AbstractLengthAwarePaginator
{
    public function __construct(
        PaginationInterface $pagination,
        private readonly iterable $iterable,
        private readonly int $total,
    ) {
        parent::__construct($pagination);
    }

    public function getTotalItems(): int
    {
        return $this->total;
    }

    protected function doGetItems(): array
    {
        return \iterator_to_array($this->iterable);
    }
}
