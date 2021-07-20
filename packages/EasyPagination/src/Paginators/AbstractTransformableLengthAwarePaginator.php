<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\TransformableLengthAwarePaginatorInterface as Transformable;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
abstract class AbstractTransformableLengthAwarePaginator extends AbstractLengthAwarePaginator implements Transformable
{
    /**
     * @var null|mixed[]
     */
    private $transformedItems;

    /**
     * @var mixed[]
     */
    private $items;

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

        // Cache items so we don't trigger SQL queries when transformer reset
        if ($this->items === null) {
            $this->items = $this->doGetItems();
        }

        return $this->transformedItems = $this->transformItems($this->items);
    }

    public function setTransformer(?callable $transformer = null): Transformable
    {
        $this->transformer = $transformer;
        // Reset transformed items
        $this->transformedItems = null;

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
