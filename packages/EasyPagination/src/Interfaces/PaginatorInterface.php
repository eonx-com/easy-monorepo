<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

use JsonSerializable;

interface PaginatorInterface extends JsonSerializable
{
    public function getCurrentPage(): int;

    /**
     * @return mixed[]
     */
    public function getItems(): array;

    public function getItemsPerPage(): int;

    public function getNextPageUrl(): string;

    public function getPageUrl(int $page): string;

    public function getPreviousPageUrl(): string;

    public function setTransformer(?callable $transformer = null): self;

    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
