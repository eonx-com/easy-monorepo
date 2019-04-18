<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Resolvers;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\EasyPagination\Traits\DataResolverTrait;

final class StartSizeAsArrayInQueryResolver implements StartSizeDataResolverInterface
{
    use DataResolverTrait;

    /**
     * @var \StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface
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
     * @param \StepTheFkUp\EasyPagination\Interfaces\StartSizeConfigInterface $config
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
     * @return \StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface
    {
        $query = $request->getQueryParams();

        return $this->createStartSizeData($this->config, $query[$this->queryAttr] ?? []);
    }
}

\class_alias(
    StartSizeAsArrayInQueryResolver::class,
    'LoyaltyCorp\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver',
    false
);
