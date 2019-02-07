<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Resolvers;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\Pagination\Traits\PagePaginationDataResolverTrait;

final class StartSizeInQueryResolver implements StartSizeDataResolverInterface
{
    use PagePaginationDataResolverTrait;

    /**
     * @var \StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface
     */
    private $config;

    /**
     * StartSizeInQueryResolver constructor.
     *
     * @param \StepTheFkUp\Pagination\Interfaces\StartSizeConfigInterface $config
     */
    public function __construct(StartSizeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Resolve pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface
    {
        return $this->createPagePaginationData($this->config, $request->getQueryParams());
    }
}