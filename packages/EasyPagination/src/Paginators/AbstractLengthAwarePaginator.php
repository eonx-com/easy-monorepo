<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;

abstract class AbstractLengthAwarePaginator extends AbstractPaginator implements LengthAwarePaginatorInterface
{
    /**
     * @var int
     */
    private $totalPages;

    public function getFirstPageUrl(): ?string
    {
        return $this->getPageUrl(1);
    }

    public function getLastPageUrl(): ?string
    {
        return $this->getPageUrl($this->getTotalPages());
    }

    public function getNextPageUrl(): ?string
    {
        return $this->hasNextPage() ? parent::getNextPageUrl() : null;
    }

    public function getPreviousPageUrl(): ?string
    {
        return $this->hasPreviousPage() ? parent::getPreviousPageUrl() : null;
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
}
