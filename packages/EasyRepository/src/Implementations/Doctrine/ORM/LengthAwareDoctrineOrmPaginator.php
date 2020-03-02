<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Paginators\AbstractLengthAwarePaginator;

/**
 * @deprecated since 2.1.5, will be removed in 3.0.0
 */
final class LengthAwareDoctrineOrmPaginator extends AbstractLengthAwarePaginator
{
    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed>
     */
    private $doctrinePaginator;

    /**
     * LengthAwareDoctrineOrmPaginator constructor.
     *
     * @param \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $doctrinePaginator
     * @param int $start
     * @param int $size
     */
    public function __construct(Paginator $doctrinePaginator, int $start, int $size)
    {
        @\trigger_error(\sprintf(
            '%s is deprecated since 2.1.5 and will be removed in 3.0, use %s instead',
            \get_class($this),
            'EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginator'
        ), \E_USER_DEPRECATED);

        $this->doctrinePaginator = $doctrinePaginator;

        parent::__construct(new StartSizeData($start, $size));
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
     * Get total number of paginated items.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->doctrinePaginator->count();
    }
}
