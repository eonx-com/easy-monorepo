<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Closure;
use EonX\EasyPagination\ValueObject\Pagination;

abstract class AbstractPaginator implements PaginatorInterface
{
    private ?array $items = null;

    private ?array $transformedItems = null;

    private ?Closure $transformer = null;

    /**
     * @var string[]
     */
    private array $urls = [];

    public function __construct(
        protected Pagination $pagination,
    ) {
        // No body needed
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

    public function getNextPageUrl(): string
    {
        return $this->getPageUrl($this->getCurrentPage() + 1);
    }

    public function getPageUrl(int $page): string
    {
        return $this->urls[$page] ??= $this->pagination->getUrl($page);
    }

    public function getPreviousPageUrl(): string
    {
        return $this->getPageUrl($this->getCurrentPage() - 1);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function setTransformer(?callable $transformer = null): PaginatorInterface
    {
        $this->transformer = $transformer === null ? null : $transformer(...);
        $this->transformedItems = null;

        return $this;
    }

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

    abstract protected function doGetItems(): array;
}
