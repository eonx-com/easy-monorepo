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

    public function getTotalItems(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        $countAlias = \sprintf('_count%s', $this->fromAlias ? \sprintf('_%s', $this->fromAlias) : '');

        $queryBuilder = $this->createQueryBuilder()->select(
            \sprintf(
                'COUNT(DISTINCT %s) as %s', // Need to distinct to remove duplicates caused by joins.
                $this->fromAlias,
                $countAlias
            )
        );

        $queryBuilder->resetDQLPart('groupBy'); // Reset groupBy to remove issue when counting.

        return $this->count = (int)($queryBuilder->getQuery()->getResult()[0][$countAlias] ?? 0);
    }

    protected function doCreateQueryBuilder(): QueryBuilder
    {
        return $this->manager->createQueryBuilder();
    }

    /**
     * @return mixed[]
     */
    protected function doGetResult(QueryBuilder $queryBuilder): array
    {
        return $queryBuilder->getQuery()->getResult();
    }
}
