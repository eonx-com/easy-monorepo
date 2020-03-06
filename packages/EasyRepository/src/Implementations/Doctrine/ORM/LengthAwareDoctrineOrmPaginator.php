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
     * @param \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $doctrinePaginator
     */
    public function __construct(Paginator $doctrinePaginator, int $start, int $size)
    {
        @\trigger_error(\sprintf(
            '%s is deprecated since 2.1.5 and will be removed in 3.0, use %s instead',
            static::class,
            'EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginator'
        ), \E_USER_DEPRECATED);

        $this->doctrinePaginator = $doctrinePaginator;

        parent::__construct(new StartSizeData($start, $size));
    }

    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        return \iterator_to_array($this->doctrinePaginator);
    }

    public function getTotalItems(): int
    {
        return $this->doctrinePaginator->count();
    }
}
