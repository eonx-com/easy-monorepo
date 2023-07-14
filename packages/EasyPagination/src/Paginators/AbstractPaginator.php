<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use Closure;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginatorInterface;

abstract class AbstractPaginator implements PaginatorInterface
{
    /**
     * @var null|mixed[]
     */
    private ?array $items = null;

    /**
     * @var null|mixed[]
     */
    private ?array $transformedItems = null;

    private ?Closure $transformer;

    /**
     * @var string[]
     */
    private array $urls = [];

    public function __construct(
        protected PaginationInterface $pagination,
    ) {
        // No body needed.
    }

    public function getCurrentPage(): int
    {
        return $this->pagination->getPage();
    }

    /**
     * @return mixed[]
     */
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

    public function getNextPageUrl(): string
    {
        return $this->getPageUrl($this->getCurrentPage() + 1);
    }

    public function getPageUrl(int $page): string
    {
        return $this->urls[$page] = $this->urls[$page] ?? $this->pagination->getUrl($page);
    }

    public function getPreviousPageUrl(): string
    {
        return $this->getPageUrl($this->getCurrentPage() - 1);
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function setTransformer(?callable $transformer = null): PaginatorInterface
    {
        $this->transformer = $transformer === null ? null : Closure::fromCallable($transformer);
        $this->transformedItems = null;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'items' => $this->getItems(),
            'pagination' => [
                $this->pagination->getPageAttribute() => $this->getCurrentPage(),
                $this->pagination->getPerPageAttribute() => $this->getItemsPerPage(),
                'nextPageUrl' => $this->getNextPageUrl(),
                'previousPageUrl' => $this->getPreviousPageUrl(),
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    abstract protected function doGetItems(): array;
}
