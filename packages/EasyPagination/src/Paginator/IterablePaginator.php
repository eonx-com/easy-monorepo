<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use EonX\EasyPagination\ValueObject\PaginationInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

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
