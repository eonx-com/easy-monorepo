<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\ApiPlatform\Paginator;

use ApiPlatform\Doctrine\Orm\Paginator;

final readonly class CustomPaginator implements CustomPaginatorInterface
{
    public function __construct(
        private Paginator $decorated,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getItems(): array
    {
        return \iterator_to_array($this->decorated->getIterator());
    }

    public function getPagination(): array
    {
        $hasNextPage = $this->decorated->getCurrentPage() < $this->decorated->getLastPage();
        $hasPreviousPage = $this->decorated->getCurrentPage() - 1 > 0;

        return [
            'currentPage' => (int)$this->decorated->getCurrentPage(),
            'hasNextPage' => $hasNextPage,
            'hasPreviousPage' => $hasPreviousPage,
            'itemsPerPage' => (int)$this->decorated->getItemsPerPage(),
            'totalItems' => (int)$this->decorated->getTotalItems(),
            'totalPages' => (int)$this->decorated->getLastPage(),
        ];
    }
}
