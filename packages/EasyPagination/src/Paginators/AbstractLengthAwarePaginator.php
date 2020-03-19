<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

abstract class AbstractLengthAwarePaginator implements LengthAwarePaginatorInterface
{
    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $start;

    public function __construct(StartSizeDataInterface $startSizeData)
    {
        $this->start = $startSizeData->getStart();
        $this->size = $startSizeData->getSize();
    }

    public function getCurrentPage(): int
    {
        return $this->start;
    }

    public function getItemsPerPage(): int
    {
        return $this->size;
    }

    public function getTotalPages(): int
    {
        return \max((int)\ceil($this->getTotalItems() / $this->getItemsPerPage()), 1);
    }

    public function hasNextPage(): bool
    {
        return $this->getTotalPages() > $this->getCurrentPage();
    }

    public function hasPreviousPage(): bool
    {
        return $this->getCurrentPage() > 1;
    }
}
