<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Traits\DoctrinePaginatorTrait;

final class DoctrineOrmLengthAwarePaginator extends AbstractTransformableLengthAwarePaginator
{
    use DoctrinePaginatorTrait;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $manager;

    /**
     * LengthAwarePaginator constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     * @param string $from
     * @param string $fromAlias
     * @param null|string $indexBy
     */
    public function __construct(
        EntityManagerInterface $manager,
        StartSizeDataInterface $startSizeData,
        string $from,
        string $fromAlias
    ) {
        $this->from = $from;
        $this->fromAlias = $fromAlias;
        $this->manager = $manager;

        parent::__construct($startSizeData);
    }

    /**
     * Create query builder and apply criteria.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQueryBuilder()
    {
        return $this->manager->createQueryBuilder();
    }

    /**
     * Class using trait must get result from given query builder.
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     */
    protected function doGetResult(QueryBuilder $queryBuilder): array
    {
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Class using trait must define how to get total items.
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param string $countAlias
     *
     * @return int
     */
    protected function doGetTotalItems(QueryBuilder $queryBuilder, string $countAlias): int
    {
        return (int)($queryBuilder->getQuery()->getResult()[0][$countAlias] ?? 0);
    }
}
