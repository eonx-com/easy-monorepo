<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Implementations\Doctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use StepTheFkUp\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface;
use StepTheFkUp\Pagination\Interfaces\LengthAwarePaginatorInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;

abstract class AbstractPaginatedDoctrineOrmRepository extends AbstractDoctrineOrmRepository implements PaginatedObjectRepositoryInterface
{
    /**
     * @var \StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface
     */
    private $startSizeData;

    /**
     * AbstractPaginatedDoctrineOrmRepository constructor.
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry
     * @param \StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface $startSizeData
     */
    public function __construct(ManagerRegistry $registry, StartSizeDataInterface $startSizeData)
    {
        $this->startSizeData = $startSizeData;

        parent::__construct($registry);
    }

    /**
     * Return a paginated list of objects managed by the repository.
     *
     * @param null|\StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \StepTheFkUp\Pagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface
    {
        return $this->doPaginate($this->createQueryBuilder()->getQuery(), $startSizeData);
    }

    /**
     * Create paginator for given query.
     *
     * @param \Doctrine\ORM\Query $query
     * @param null|\StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface $startSizeData
     * @param null|bool $fetchJoinCollection
     *
     * @return \StepTheFkUp\Pagination\Interfaces\LengthAwarePaginatorInterface
     */
    protected function doPaginate(
        Query $query,
        ?StartSizeDataInterface $startSizeData = null,
        ?bool $fetchJoinCollection = null
    ): LengthAwarePaginatorInterface {
        $startSizeData = $startSizeData ?? $this->startSizeData;

        $start = $startSizeData->getStart();
        $size = $startSizeData->getSize();

        $query
            ->setFirstResult(($start - 1) * $size)
            ->setMaxResults($size);

        return new LengthAwareDoctrineOrmPaginator(
            new DoctrinePaginator($query, $fetchJoinCollection ?? true),
            $start,
            $size
        );
    }
}
