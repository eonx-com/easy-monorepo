<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

final class EmptyPaginator extends AbstractLengthAwarePaginator
{
    /**
     * Get current items being paginated.
     *
     * @return mixed[]
     */
    public function getItems(): array
    {
        return [];
    }

    /**
     * Get total number of paginated items.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return 0;
    }
}
