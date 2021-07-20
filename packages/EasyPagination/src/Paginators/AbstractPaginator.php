<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginatorInterface;
use Laminas\Uri\Uri;

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

    private function getPageUrl(int $page): string
    {
        $uri = new Uri($this->pagination->getUrl());
        $query = $uri->getQueryAsArray();

        $query[$this->pagination->getPageAttribute()] = $page > 0 ? $page : 1;
        $query[$this->pagination->getPerPageAttribute()] = $this->pagination->getPerPage();

        return $uri->setQuery($query)->toString();
    }
}
