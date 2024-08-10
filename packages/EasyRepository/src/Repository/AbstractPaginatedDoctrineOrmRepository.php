<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyPagination\Paginator\DoctrineOrmLengthAwarePaginator;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyPagination\ValueObject\Pagination;
use EonX\EasyRepository\Repository\PaginatedObjectRepositoryInterface as RepoInterface;

abstract class AbstractPaginatedDoctrineOrmRepository extends AbstractDoctrineOrmRepository implements RepoInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Pagination $pagination,
    ) {
        parent::__construct($registry);
    }

    public function paginate(?Pagination $pagination = null): LengthAwarePaginatorInterface
    {
        return $this->createLengthAwarePaginator(null, null, $pagination);
    }

    protected function addPaginationToQuery(Query $query, ?Pagination $pagination = null): void
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
        ?Pagination $pagination = null,
    ): DoctrineOrmLengthAwarePaginator {
        return new DoctrineOrmLengthAwarePaginator(
            $pagination ?? $this->pagination,
            $this->manager,
            $from ?? $this->getEntityClass(),
            $fromAlias ?? $this->getEntityAlias()
        );
    }
}
