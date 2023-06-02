<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginator;
use EonX\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface as RepoInterface;

abstract class AbstractPaginatedDoctrineOrmRepository extends AbstractDoctrineOrmRepository implements RepoInterface
{
    private PaginationInterface $pagination;

    public function __construct(ManagerRegistry $registry, PaginationInterface $pagination)
    {
        $this->pagination = $pagination;

        parent::__construct($registry);
    }

    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorInterface
    {
        return $this->createLengthAwarePaginator(null, null, $pagination);
    }

    protected function addPaginationToQuery(Query $query, ?PaginationInterface $pagination = null): void
    {
        $pagination = $pagination ?? $this->pagination;

        $page = $pagination->getPage();
        $perPage = $pagination->getPerPage();

        $query
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);
    }

    protected function createLengthAwarePaginator(
        ?string $from = null,
        ?string $fromAlias = null,
        ?PaginationInterface $pagination = null,
    ): DoctrineOrmLengthAwarePaginator {
        return new DoctrineOrmLengthAwarePaginator(
            $pagination ?? $this->pagination,
            $this->manager,
            $from ?? $this->getEntityClass(),
            $fromAlias ?? $this->getEntityAlias()
        );
    }
}
