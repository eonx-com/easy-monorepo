<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers;

use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Traits\DataResolverTrait;
use Psr\Http\Message\ServerRequestInterface;

final class StartSizeAsArrayInQueryResolver implements StartSizeDataResolverInterface
{
    use DataResolverTrait;

    /**
     * @var \EonX\EasyPagination\Interfaces\StartSizeConfigInterface
     */
    private $config;

    /**
     * The name of the query attribute containing the pagination data array.
     *
     * @var string
     */
    private $queryAttr;

    public function __construct(StartSizeConfigInterface $config, string $queryAttr)
    {
        $this->config = $config;
        $this->queryAttr = $queryAttr;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function resolve($request): StartSizeDataInterface
    {
        $queryAttr = $request instanceof ServerRequestInterface
            ? $request->getQueryParams()[$this->queryAttr] ?? []
            : $request->query->get($this->queryAttr, []);

        return $this->createStartSizeData($this->config, $queryAttr, $request);
    }
}
