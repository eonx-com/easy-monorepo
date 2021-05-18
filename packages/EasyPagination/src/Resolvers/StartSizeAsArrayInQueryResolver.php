<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers;

use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Traits\DataResolverTrait;
use Symfony\Component\HttpFoundation\Request;

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

    public function resolve(Request $request): StartSizeDataInterface
    {
        $queryAttr = $request->query->get($this->queryAttr, []);

        return $this->createStartSizeData($this->config, $queryAttr, $request);
    }
}
