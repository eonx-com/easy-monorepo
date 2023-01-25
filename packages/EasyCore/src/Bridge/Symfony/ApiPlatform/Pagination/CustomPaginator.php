<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;

final class CustomPaginator implements CustomPaginatorInterface
{
    /**
     * @var \ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator<mixed>
     */
    private $decorated;

    /**
     * @param \ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator<mixed> $decorated
     */
    public function __construct(Paginator $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @return mixed[]
     *
     * @throws \Exception
     */
    public function getItems(): array
    {
        return \iterator_to_array($this->decorated->getIterator());
    }

    /**
     * @return mixed[]
     */
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
