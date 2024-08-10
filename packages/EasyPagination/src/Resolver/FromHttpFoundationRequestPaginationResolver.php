<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Resolver;

use EonX\EasyPagination\Provider\PaginationConfigProviderInterface;
use EonX\EasyPagination\ValueObject\Pagination;
use Symfony\Component\HttpFoundation\Request;

final readonly class FromHttpFoundationRequestPaginationResolver
{
    public function __construct(
        private PaginationConfigProviderInterface $config,
        private Request $request,
    ) {
        // No body needed
    }

    public function __invoke(): Pagination
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
