<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
interface TransformableLengthAwarePaginatorInterface extends LengthAwarePaginatorInterface
{
    public function setTransformer(?callable $transformer = null): self;
}
