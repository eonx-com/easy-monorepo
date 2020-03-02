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
     * Get current items being paginated.
     *
     * @return mixed[]
     */
    public function getItems(): array
    {
        if ($this->transformedItems !== null) {
            return $this->transformedItems;
        }

        return $this->transformedItems = $this->transformItems($this->doGetItems());
    }

    /**
     * Set transformer to transform each item.
     *
     * @param null|callable $transformer
     *
     * @return \EonX\EasyPagination\Interfaces\TransformableLengthAwarePaginatorInterface
     */
    public function setTransformer(?callable $transformer = null): Transformable
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Children classes must implement getItems themselves.
     *
     * @return mixed[]
     */
    abstract protected function doGetItems(): array;

    /**
     * Transform given items if transformer set.
     *
     * @param mixed[] $items
     *
     * @return mixed[]
     */
    protected function transformItems(array $items): array
    {
        return $this->transformer === null ? $items : \array_map($this->transformer, $items);
    }
}
