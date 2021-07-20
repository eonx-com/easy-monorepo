<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
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
