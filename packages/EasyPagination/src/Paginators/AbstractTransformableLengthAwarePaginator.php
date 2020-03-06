<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\TransformableLengthAwarePaginatorInterface as Transformable;

abstract class AbstractTransformableLengthAwarePaginator extends AbstractLengthAwarePaginator implements Transformable
{
    /**
     * @var mixed[]
     */
    private $transformedItems;

    /**
     * @var null|callable
     */
    private $transformer;

    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        if ($this->transformedItems !== null) {
            return $this->transformedItems;
        }

        return $this->transformedItems = $this->transformItems($this->doGetItems());
    }

    public function setTransformer(?callable $transformer = null): Transformable
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @return mixed[]
     */
    abstract protected function doGetItems(): array;

    /**
     * @param mixed[] $items
     *
     * @return mixed[]
     */
    protected function transformItems(array $items): array
    {
        return $this->transformer === null ? $items : \array_map($this->transformer, $items);
    }
}
