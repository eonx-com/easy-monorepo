<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginatorInterface;

abstract class AbstractPaginator implements PaginatorInterface
{
    /**
     * @var mixed[]
     */
    private $items;

    /**
     * @var \EonX\EasyPagination\Interfaces\PaginationInterface
     */
    private $pagination;

    /**
     * @var null|mixed[]
     */
    private $transformedItems;

    /**
     * @var null|callable
     */
    private $transformer;

    /**
     * @var string[]
     */
    private $urls = [];

    public function __construct(PaginationInterface $pagination)
    {
        $this->pagination = $pagination;
    }

    public function getCurrentPage(): int
    {
        return $this->pagination->getPage();
    }

    public function getItems(): array
    {
        if ($this->transformedItems !== null) {
            return $this->transformedItems;
        }

        if ($this->items === null) {
            $this->items = $this->doGetItems();
        }

        return $this->transformedItems = $this->transformer !== null
            ? \array_map($this->transformer, $this->items)
            : $this->items;
    }

    public function getItemsPerPage(): int
    {
        return $this->pagination->getPerPage();
    }

    public function getNextPageUrl(): ?string
    {
        return $this->getPageUrl($this->getCurrentPage() + 1);
    }

    public function getPageUrl(int $page): string
    {
        return $this->urls[$page] = $this->urls[$page] ?? $this->pagination->getUrl($page);
    }

    public function getPreviousPageUrl(): ?string
    {
        return $this->getPageUrl($this->getCurrentPage() - 1);
    }

    public function setTransformer(?callable $transformer = null): PaginatorInterface
    {
        $this->transformer = $transformer;
        $this->transformedItems = null;

        return $this;
    }

    /**
     * @return mixed[]
     */
    abstract protected function doGetItems(): array;
}
