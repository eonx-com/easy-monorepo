<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Implementations\Doctrine\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator;
use StepTheFkUp\Pagination\Interfaces\LengthAwarePaginatorInterface;

final class LengthAwareDoctrineOrmPaginator implements LengthAwarePaginatorInterface
{
    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private $doctrinePaginator;

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $size;

    /**
     * LengthAwareDoctrineOrmPaginator constructor.
     *
     * @param \Doctrine\ORM\Tools\Pagination\Paginator $doctrinePaginator
     * @param int $start
     * @param int $size
     */
    public function __construct(Paginator $doctrinePaginator, int $start, int $size)
    {
        $this->doctrinePaginator = $doctrinePaginator;
        $this->start = $start;
        $this->size = $size;
    }

    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->start;
    }

    /**
     * Get current items being paginated.
     *
     * @return mixed[]
     */
    public function getItems(): array
    {
        return \iterator_to_array($this->doctrinePaginator);
    }

    /**
     * Get items to be shown per page.
     *
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->size;
    }

    /**
     * Get total number of paginated items.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->doctrinePaginator->count();
    }

    /**
     * Get total number of pages based on the total number of items.
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return \max((int)\ceil($this->getTotalItems() / $this->size), 1);
    }

    /**
     * When current page has a next page.
     *
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->getTotalPages() > $this->getCurrentPage();
    }

    /**
     * When current page has a previous page.
     *
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->getCurrentPage() > 1;
    }
}
