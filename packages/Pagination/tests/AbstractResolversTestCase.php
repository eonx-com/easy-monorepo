<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig;
use Zend\Diactoros\ServerRequestFactory;

abstract class AbstractResolversTestCase extends AbstractTestCase
{
    /**
     * Create PagePaginationConfig.
     *
     * @param null|string $numberAttr
     * @param null|int $numberDefault
     * @param null|string $sizeAttr
     * @param null|int $sizeDefault
     *
     * @return \StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig
     */
    protected function createPagePaginationConfig(
        ?string $numberAttr = null,
        ?int $numberDefault = null,
        ?string $sizeAttr = null,
        ?int $sizeDefault = null
    ): PagePaginationConfig {
        return new PagePaginationConfig(
            $numberAttr ?? 'number',
            $numberDefault ?? 1,
            $sizeAttr ?? 'size',
            $sizeDefault ?? 15
        );
    }

    /**
     * Create server request for given query.
     *
     * @param null|mixed[] $query
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function createServerRequest(?array $query = null): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals(null, $query);
    }
}