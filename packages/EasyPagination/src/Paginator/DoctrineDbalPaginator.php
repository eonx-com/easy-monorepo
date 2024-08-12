<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\DBAL\Connection;
use EonX\EasyPagination\Paginator\ExtendablePaginatorInterface as ExtendableInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;

final class DoctrineDbalPaginator extends AbstractPaginator implements ExtendableInterface
{
    use DoctrineDbalPaginatorTrait;

    public function __construct(
        PaginationInterface $pagination,
        Connection $connection,
        string $from,
        ?string $fromAlias = null,
    ) {
        $this->conn = $connection;
        $this->from = $from;
        $this->fromAlias = $fromAlias;

        parent::__construct($pagination);
    }
}
