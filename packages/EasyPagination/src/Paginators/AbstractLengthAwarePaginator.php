<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;

abstract class AbstractLengthAwarePaginator extends AbstractPaginator implements LengthAwarePaginatorInterface
{
    private ?int $totalPages = null;

    public function getFirstPageUrl(): string
    {
        return $this->getPageUrl(1);
    }

    public function getLastPageUrl(): string
    {
        return $this->getPageUrl($this->getTotalPages());
    }

    public function getTotalPages(): int
    {
        if ($this->totalPages !== null) {
            return $this->totalPages;
        }

        return $this->totalPages = \max((int)\ceil($this->getTotalItems() / $this->getItemsPerPage()), 1);
    }

    public function hasNextPage(): bool
    {
        return $this->getTotalPages() > $this->getCurrentPage();
    }

    public function hasPreviousPage(): bool
    {
        return $this->getCurrentPage() > 1;
    }

    public function toArray(): array
    {
        return \array_merge_recursive(parent::toArray(), [
            'pagination' => [
                'firstPageUrl' => $this->getFirstPageUrl(),
                'hasNextPage' => $this->hasNextPage(),
                'hasPreviousPage' => $this->hasPreviousPage(),
                'lastPageUrl' => $this->getLastPageUrl(),
                'totalPages' => $this->getTotalPages(),
            ],
        ]);
    }
}
