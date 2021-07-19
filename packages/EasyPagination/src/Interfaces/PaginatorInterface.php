<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface PaginatorInterface
{
    public function getCurrentPage(): int;

    /**
     * @return mixed[]
     */
    public function getItems(): array;

    public function getItemsPerPage(): int;

    public function getNextPageUrl(): ?string;

    public function getPreviousPageUrl(): ?string;

    public function setTransformer(?callable $transformer = null): self;
}
