<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface TransformableLengthAwarePaginatorInterface extends LengthAwarePaginatorInterface
{
    public function setTransformer(?callable $transformer = null): self;
}
