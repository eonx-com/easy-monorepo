<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\DBAL\Connection;
use EonX\EasyPagination\Paginator\ExtendablePaginatorInterface as ExtendableInterface;
use EonX\EasyPagination\ValueObject\Pagination;

final class DoctrineDbalLengthAwarePaginator extends AbstractLengthAwarePaginator implements ExtendableInterface
{
    use DoctrineDbalPaginatorTrait;

    public function __construct(
        Pagination $pagination,
        Connection $conn,
        string $from,
        ?string $fromAlias = null,
    ) {
        $this->conn = $conn;
        $this->from = $from;
        $this->fromAlias = $fromAlias;

        parent::__construct($pagination);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTotalItems(): int
    {
        return $this->doGetTotalItems();
    }
}
