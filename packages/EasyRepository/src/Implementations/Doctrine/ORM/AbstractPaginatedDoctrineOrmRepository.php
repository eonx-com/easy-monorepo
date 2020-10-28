<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginator;
use EonX\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface as RepoInterface;

abstract class AbstractPaginatedDoctrineOrmRepository extends AbstractDoctrineOrmRepository implements RepoInterface
{
    /**
     * @var \EonX\EasyPagination\Interfaces\StartSizeDataInterface
     */
    private $startSizeData;

    public function __construct(ManagerRegistry $registry, StartSizeDataInterface $startSizeData)
    {
        $this->startSizeData = $startSizeData;

        parent::__construct($registry);
    }

    public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface
    {
        return $this->createLengthAwarePaginator(null, null, $startSizeData);
    }

    protected function addPaginationToQuery(Query $query, ?StartSizeDataInterface $startSizeData = null): void
    {
        $startSizeData = $startSizeData ?? $this->startSizeData;

        $start = $startSizeData->getStart();
        $size = $startSizeData->getSize();

        $query
            ->setFirstResult(($start - 1) * $size)
            ->setMaxResults($size);
    }

    protected function createLengthAwarePaginator(
        ?string $from = null,
        ?string $fromAlias = null,
        ?StartSizeDataInterface $startSizeData = null
    ): DoctrineOrmLengthAwarePaginator {
        return new DoctrineOrmLengthAwarePaginator(
            $this->manager,
            $startSizeData ?? $this->startSizeData,
            $from ?? $this->getEntityClass(),
            $fromAlias ?? $this->getEntityAlias()
        );
    }

    protected function doPaginate(
        Query $query,
        ?StartSizeDataInterface $startSizeData = null,
        ?bool $fetchJoinCollection = null
    ): LengthAwarePaginatorInterface {
        @\trigger_error(\sprintf(
            '%s::%s() is deprecated since 2.1.5 and will be removed in 3.0, use %s::%s() instead',
            static::class,
            __METHOD__,
            static::class,
            'createLengthAwarePaginator'
        ), \E_USER_DEPRECATED);

        $this->addPaginationToQuery($query, $startSizeData);

        $startSizeData = $startSizeData ?? $this->startSizeData;

        return new LengthAwareDoctrineOrmPaginator(
            new DoctrinePaginator($query, $fetchJoinCollection ?? true),
            $startSizeData->getStart(),
            $startSizeData->getSize()
        );
    }
}
