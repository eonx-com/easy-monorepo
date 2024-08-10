<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use EonX\EasyPagination\Paginator\ExtendablePaginatorInterface as ExtendableInterface;
use EonX\EasyPagination\ValueObject\Pagination;
use Illuminate\Database\Eloquent\Model;

final class EloquentPaginator extends AbstractPaginator implements ExtendableInterface
{
    use EloquentPaginatorTrait;

    public function __construct(Pagination $pagination, Model $model)
    {
        $this->model = $model;

        parent::__construct($pagination);
    }
}
