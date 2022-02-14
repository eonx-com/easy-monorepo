<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorNewInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginatorNew;
use EonX\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface as RepoInterface;

abstract class AbstractPaginatedDoctrineOrmRepository extends AbstractDoctrineOrmRepository implements RepoInterface
{
    /**
     * @var \EonX\EasyPagination\Interfaces\PaginationInterface
     */
    private $pagination;

    public function __construct(ManagerRegistry $registry, PaginationInterface $pagination)
    {
        $this->pagination = $pagination;

        parent::__construct($registry);
    }

    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorNewInterface
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
        ?PaginationInterface $pagination = null
    ): DoctrineOrmLengthAwarePaginatorNew {
        return new DoctrineOrmLengthAwarePaginatorNew(
            $pagination ?? $this->pagination,
            $this->manager,
            $from ?? $this->getEntityClass(),
            $fromAlias ?? $this->getEntityAlias()
        );
    }
}
