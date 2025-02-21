<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

abstract class AbstractLengthAwarePaginator extends AbstractPaginator implements LengthAwarePaginatorInterface
{
    private bool $largeDatasetEnabled = false;

    private int $largeDatasetPaginationPreciseResultsLimit = 100_000;

    private ?int $totalPages = null;

    public function getFirstPageUrl(): string
    {
        return $this->getPageUrl(1);
    }

    public function getLastPageUrl(): string
    {
        return $this->getPageUrl($this->getTotalPages());
    }

    public function getLargeDatasetPaginationPreciseResultsLimit(): int
    {
        return $this->largeDatasetPaginationPreciseResultsLimit;
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

    public function isLargeDatasetEnabled(): bool
    {
        return $this->largeDatasetEnabled;
    }

    public function setLargeDatasetEnabled(?bool $largeDatasetEnabled = null): self
    {
        $this->largeDatasetEnabled = $largeDatasetEnabled ?? true;

        return $this;
    }

    public function setLargeDatasetPaginationPreciseResultsLimit(int $largeDatasetPaginationPreciseResultsLimit): self
    {
        $this->largeDatasetPaginationPreciseResultsLimit = $largeDatasetPaginationPreciseResultsLimit;

        return $this;
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
