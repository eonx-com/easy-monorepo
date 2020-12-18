<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Resolvers;

use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Traits\DataResolverTrait;
use Symfony\Component\HttpFoundation\Request;

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

    public function resolve(Request $request): StartSizeDataInterface
    {
        $queryParams = $request->query->all();

        return $this->createStartSizeData($this->config, $queryParams, $request);
    }
}
