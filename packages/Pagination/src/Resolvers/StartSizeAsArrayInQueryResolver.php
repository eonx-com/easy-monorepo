<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Resolvers;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\Pagination\Traits\PagePaginationDataResolverTrait;

final class StartSizeAsArrayInQueryResolver implements StartSizeDataResolverInterface
{
    use PagePaginationDataResolverTrait;

    /**
     * @var \StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface
     */
    private $config;

    /**
     * The name of the query attribute containing the pagination data array.
     *
     * @var string
     */
    private $queryAttr;

    /**
     * StartSizeAsArrayInQueryResolver constructor.
     *
     * @param \StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface $config
     * @param string $queryAttr
     */
    public function __construct(StartSizeConfigInterface $config, string $queryAttr)
    {
        $this->config = $config;
        $this->queryAttr = $queryAttr;
    }

    /**
     * Resolve page pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface
    {
        $query = $request->getQueryParams();

        return $this->createPagePaginationData($this->config, $query[$this->queryAttr] ?? []);
    }
}