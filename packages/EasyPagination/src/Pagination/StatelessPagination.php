<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Pagination;

use EonX\EasyPagination\Provider\PaginationProviderInterface;

final readonly class StatelessPagination implements PaginationInterface
{
    public function __construct(
        private PaginationProviderInterface $paginationProvider
    ) {
    }

    public function getPage(): int
    {
        return $this->paginationProvider->getPagination()
            ->getPage();
    }

    public function getPageAttribute(): string
    {
        return $this->paginationProvider->getPagination()
            ->getPageAttribute();
    }

    public function getPerPage(): int
    {
        return $this->paginationProvider->getPagination()
            ->getPerPage();
    }

    public function getPerPageAttribute(): string
    {
        return $this->paginationProvider->getPagination()
            ->getPerPageAttribute();
    }

    public function getUrl(int $page): string
    {
        return $this->paginationProvider->getPagination()
            ->getUrl($page);
    }

    public function setUrlResolver(?callable $urlResolver = null): PaginationInterface
    {
        return $this->paginationProvider->getPagination()
            ->setUrlResolver($urlResolver);
    }
}
