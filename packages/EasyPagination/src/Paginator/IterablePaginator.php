<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;

final class IterablePaginator extends AbstractPaginator
{
    public function __construct(
        PaginationInterface $pagination,
        private readonly iterable $iterable,
    ) {
        parent::__construct($pagination);
    }

    protected function doGetItems(): array
    {
        return CollectorHelper::convertToArray($this->iterable);
    }
}
