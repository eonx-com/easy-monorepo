<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

final class EmptyPaginator extends AbstractLengthAwarePaginator
{
    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        return [];
    }

    public function getTotalItems(): int
    {
        return 0;
    }
}
