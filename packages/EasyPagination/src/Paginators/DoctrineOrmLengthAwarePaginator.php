<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EonX\EasyPagination\Exceptions\InvalidPrimaryKeyIndexException;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Traits\DoctrinePaginatorTrait;

/**
 * @deprecated since 3.2, will be removed in 4.0. Will be replace by new implementation using Pagination.
 */
final class DoctrineOrmLengthAwarePaginator extends AbstractTransformableLengthAwarePaginator
{
    use DoctrinePaginatorTrait;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $manager;

    /**
     * @var string
     */
    private $primaryKeyIndex;

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

    protected function doCreateQueryBuilder(): QueryBuilder
    {
        return $this->manager->createQueryBuilder();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     */
    protected function doGetResult($queryBuilder): array
    {
        return $queryBuilder->getQuery()
            ->getResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function doGetTotalItems($queryBuilder, string $countAlias): int
    {
        return (int)($queryBuilder->getQuery()->getResult()[0][$countAlias] ?? 0);
    }

    protected function getPrimaryKeyIndex(): string
    {
        if ($this->primaryKeyIndex !== null) {
            return $this->primaryKeyIndex;
        }

        try {
            return $this->primaryKeyIndex = $this->manager
                ->getClassMetadata($this->from)
                ->getSingleIdentifierColumnName();
        } catch (\Throwable $throwable) {
            throw new InvalidPrimaryKeyIndexException($throwable->getMessage());
        }
    }
}
