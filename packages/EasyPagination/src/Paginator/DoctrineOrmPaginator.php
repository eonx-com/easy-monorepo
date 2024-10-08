<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\ExtendablePaginatorInterface as ExtendableInterface;

final class DoctrineOrmPaginator extends AbstractPaginator implements ExtendableInterface
{
    use DoctrineOrmPaginatorTrait;

    /**
     * @param class-string $from
     */
    public function __construct(
        PaginationInterface $pagination,
        EntityManagerInterface $manager,
        string $from,
        string $fromAlias,
        ?string $indexBy = null,
    ) {
        $this->manager = $manager;
        $this->from = $from;
        $this->fromAlias = $fromAlias;
        $this->indexBy = $indexBy;

        parent::__construct($pagination);
    }
}
