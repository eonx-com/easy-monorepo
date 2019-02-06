<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Resolvers;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataResolverInterface;
use StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig;
use StepTheFkUp\Pagination\Traits\PagePaginationDataResolverTrait;

final class PageAsArrayInQueryResolver implements PagePaginationDataResolverInterface
{
    use PagePaginationDataResolverTrait;

    /**
     * @var \StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig
     */
    private $config;

    /**
     * The name of the query attribute containing the pagination data array.
     *
     * @var string
     */
    private $queryAttr;

    /**
     * PageAsArrayInQueryResolver constructor.
     *
     * @param \StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig $config
     * @param string $queryAttr
     */
    public function __construct(PagePaginationConfig $config, string $queryAttr)
    {
        $this->config = $config;
        $this->queryAttr = $queryAttr;
    }

    /**
     * Resolve page pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface
     */
    public function resolve(ServerRequestInterface $request): PagePaginationDataInterface
    {
        $query = $request->getQueryParams();

        return $this->createPagePaginationData($this->config, $query[$this->queryAttr] ?? []);
    }
}