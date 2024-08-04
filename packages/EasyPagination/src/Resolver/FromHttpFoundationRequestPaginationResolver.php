<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Resolver;

use EonX\EasyPagination\ValueObject\Pagination;
use EonX\EasyPagination\ValueObject\PaginationConfigInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class FromHttpFoundationRequestPaginationResolver
{
    public function __construct(
        private PaginationConfigInterface $config,
        private Request $request,
    ) {
        // No body needed
    }

    public function __invoke(): PaginationInterface
    {
        $query = $this->request->query;

        return Pagination::create(
            (int)$query->get($this->config->getPageAttribute(), $this->config->getPageDefault()),
            (int)$query->get($this->config->getPerPageAttribute(), $this->config->getPerPageDefault()),
            $this->config->getPageAttribute(),
            $this->config->getPerPageAttribute(),
            $this->request->getUri()
        );
    }
}
