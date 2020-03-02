<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface TransformableLengthAwarePaginatorInterface extends LengthAwarePaginatorInterface
{
    /**
     * Set transformer to transform each item.
     *
     * @param null|callable $transformer
     *
     * @return \EonX\EasyPagination\Interfaces\TransformableLengthAwarePaginatorInterface
     */
    public function setTransformer(?callable $transformer = null): self;
}
