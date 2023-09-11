<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\ExtendablePaginatorInterface as ExtendableInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Traits\EloquentPaginatorTrait;
use Illuminate\Database\Eloquent\Model;

final class EloquentPaginator extends AbstractPaginator implements ExtendableInterface
{
    use EloquentPaginatorTrait;

    public function __construct(PaginationInterface $pagination, Model $model)
    {
        $this->model = $model;

        parent::__construct($pagination);
    }
}
