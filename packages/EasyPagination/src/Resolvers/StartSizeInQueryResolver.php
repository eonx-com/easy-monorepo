<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Resolvers;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\EasyPagination\Traits\DataResolverTrait;

final class StartSizeInQueryResolver implements StartSizeDataResolverInterface
{
    use DataResolverTrait;

    /**
     * @var \StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface
     */
    private $config;

    /**
     * StartSizeInQueryResolver constructor.
     *
     * @param \StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface $config
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
     * @return \StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface
    {
        return $this->createStartSizeData($this->config, $request->getQueryParams());
    }
}

\class_alias(
    StartSizeInQueryResolver::class,
    'LoyaltyCorp\EasyPagination\Resolvers\StartSizeInQueryResolver',
    false
);
