<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\ExtendablePaginatorInterface as ExtendableInterface;
use Illuminate\Database\Eloquent\Model;

final class EloquentLengthAwarePaginator extends AbstractLengthAwarePaginator implements ExtendableInterface
{
    use EloquentPaginatorTrait;

    public function __construct(PaginationInterface $pagination, Model $model)
    {
        $this->model = $model;

        parent::__construct($pagination);
    }

    public function getTotalItems(): int
    {
        return $this->doGetTotalItems();
    }
}
