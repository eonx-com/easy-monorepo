<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\DoctrineOrmLengthAwarePaginator;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyRepository\Repository\PaginatedObjectRepositoryInterface as RepoInterface;

abstract class AbstractPaginatedDoctrineOrmRepository extends AbstractDoctrineOrmRepository implements RepoInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginationInterface $pagination,
    ) {
        parent::__construct($registry);
    }

    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorInterface
    {
        return $this->createLengthAwarePaginator(null, null, $pagination);
    }

    protected function addPaginationToQuery(Query $query, ?PaginationInterface $pagination = null): void
    {
        $pagination ??= $this->pagination;

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
