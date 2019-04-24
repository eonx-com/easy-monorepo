<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Resolvers;

use LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use LoyaltyCorp\EasyPagination\Traits\DataResolverTrait;
use Psr\Http\Message\ServerRequestInterface;

final class StartSizeInQueryResolver implements StartSizeDataResolverInterface
{
    use DataResolverTrait;

    /**
     * @var \LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface
     */
    private $config;

    /**
     * StartSizeInQueryResolver constructor.
     *
     * @param \LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface $config
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
     * @return \LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface
    {
        return $this->createStartSizeData($this->config, $request->getQueryParams());
    }
}

\class_alias(
    StartSizeInQueryResolver::class,
    'StepTheFkUp\EasyPagination\Resolvers\StartSizeInQueryResolver',
    false
);
