<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use Doctrine\DBAL\Connection;
use EonX\EasyPagination\Interfaces\ExtendablePaginatorInterface as ExtendableInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Traits\DoctrineDbalPaginatorTrait;

final class DoctrineDbalPaginator extends AbstractPaginator implements ExtendableInterface
{
    use DoctrineDbalPaginatorTrait;

    public function __construct(
        PaginationInterface $pagination,
        Connection $conn,
        string $from,
        ?string $fromAlias = null
    ) {
        $this->conn = $conn;
        $this->from = $from;
        $this->fromAlias = $fromAlias;

        parent::__construct($pagination);
    }
}
