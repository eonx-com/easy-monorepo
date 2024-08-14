<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\DBAL\Connection;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\ExtendablePaginatorInterface as ExtendableInterface;

final class DoctrineDbalPaginator extends AbstractPaginator implements ExtendableInterface
{
    use DoctrineDbalPaginatorTrait;

    public function __construct(
        PaginationInterface $pagination,
        Connection $connection,
        string $from,
        ?string $fromAlias = null,
    ) {
        $this->connection = $connection;
        $this->from = $from;
        $this->fromAlias = $fromAlias;

        parent::__construct($pagination);
    }
}
