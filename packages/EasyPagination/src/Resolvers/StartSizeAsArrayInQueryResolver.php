<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Resolvers;

use LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use LoyaltyCorp\EasyPagination\Traits\DataResolverTrait;
use Psr\Http\Message\ServerRequestInterface;

final class StartSizeAsArrayInQueryResolver implements StartSizeDataResolverInterface
{
    use DataResolverTrait;

    /**
     * @var \LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface
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
     * @param \LoyaltyCorp\EasyPagination\Interfaces\StartSizeConfigInterface $config
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
     * @return \LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface
    {
        $query = $request->getQueryParams();

        return $this->createStartSizeData($this->config, $query[$this->queryAttr] ?? []);
    }
}

\class_alias(
    StartSizeAsArrayInQueryResolver::class,
    'StepTheFkUp\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver',
    false
);
