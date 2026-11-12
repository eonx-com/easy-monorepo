<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Resolver;

use EonX\EasyPagination\Pagination\Pagination;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Provider\PaginationConfigProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class FromHttpFoundationRequestPaginationResolver
{
    public function __construct(
        private PaginationConfigProviderInterface $paginationConfigProvider,
        private Request $request,
    ) {
        // No body needed
    }

    public function __invoke(): PaginationInterface
    {
        $query = $this->request->query;
        $config = $this->paginationConfigProvider;

        $page = (int)$query->get($config->getPageAttribute(), $config->getPageDefault());
        $perPage = (int)$query->get($config->getPerPageAttribute(), $config->getPerPageDefault());

        // A page or page size below 1 is a client error (an SQL error, a division by zero or no limit at all),
        // so the default is used instead
        if ($page < 1) {
            $page = $config->getPageDefault();
        }

        if ($perPage < 1) {
            $perPage = $config->getPerPageDefault();
        }

        // @todo Call it directly in 7.0, when the interface declares it
        $perPageMax = \method_exists($config, 'getPerPageMax') ? $config->getPerPageMax() : null;

        if ($perPageMax !== null && $perPage > $perPageMax) {
            $perPage = $perPageMax;
        }

        return Pagination::create(
            $page,
            $perPage,
            $config->getPageAttribute(),
            $config->getPerPageAttribute(),
            $this->request->getUri()
        );
    }
}
