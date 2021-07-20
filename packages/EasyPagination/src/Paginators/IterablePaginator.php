<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyUtils\CollectorHelper;

final class IterablePaginator extends AbstractPaginator
{
    /**
     * @var iterable<mixed>
     */
    private $iterable;

    /**
     * @param iterable<mixed> $iterable
     */
    public function __construct(PaginationInterface $pagination, iterable $iterable)
    {
        $this->iterable = $iterable;

        parent::__construct($pagination);
    }

    /**
     * @return mixed[]
     */
    protected function doGetItems(): array
    {
        return CollectorHelper::convertToArray($this->iterable);
    }
}
