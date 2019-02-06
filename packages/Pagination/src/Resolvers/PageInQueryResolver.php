<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Resolvers;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataResolverInterface;
use StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig;
use StepTheFkUp\Pagination\Traits\PagePaginationDataResolverTrait;

final class PageInQueryResolver implements PagePaginationDataResolverInterface
{
    use PagePaginationDataResolverTrait;

    /**
     * @var \StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig
     */
    private $config;

    /**
     * PagePerPageInQueryResolver constructor.
     *
     * @param \StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig $config
     */
    public function __construct(PagePaginationConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Resolve pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface
     */
    public function resolve(ServerRequestInterface $request): PagePaginationDataInterface
    {
        return $this->createPagePaginationData($this->config, $request->getQueryParams());
    }
}