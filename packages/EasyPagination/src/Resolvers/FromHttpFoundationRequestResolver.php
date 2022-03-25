<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers;

use EonX\EasyPagination\Interfaces\PaginationConfigInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use Symfony\Component\HttpFoundation\Request;

final class FromHttpFoundationRequestResolver
{
    public function __construct(private PaginationConfigInterface $config, private Request $request)
    {
        // No body needed.
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
