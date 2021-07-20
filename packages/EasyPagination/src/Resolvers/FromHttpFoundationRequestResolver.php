<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\PaginationConfig;
use Symfony\Component\HttpFoundation\Request;

final class FromHttpFoundationRequestResolver
{
    /**
     * @var \EonX\EasyPagination\PaginationConfig
     */
    private $config;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(PaginationConfig $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    public function __invoke(): PaginationInterface
    {
        return Pagination::create(
            $this->request->get($this->config->getPageAttribute(), $this->config->getPageDefault()),
            $this->request->get($this->config->getPerPageAttribute(), $this->config->getPerPageDefault()),
            $this->config->getPageAttribute(),
            $this->config->getPerPageAttribute(),
            $this->request->getUri()
        );
    }
}
