<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers;

use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Traits\DataResolverTrait;
use Psr\Http\Message\ServerRequestInterface;

final class StartSizeInQueryResolver implements StartSizeDataResolverInterface
{
    use DataResolverTrait;

    /**
     * @var \EonX\EasyPagination\Interfaces\StartSizeConfigInterface
     */
    private $config;

    public function __construct(StartSizeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function resolve($request): StartSizeDataInterface
    {
        $queryParams = $request instanceof ServerRequestInterface
            ? $request->getQueryParams()
            : $request->query->all();

        return $this->createStartSizeData($this->config, $queryParams, $request);
    }
}
