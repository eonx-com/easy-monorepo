<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Resolvers;

use StepTheFkUp\Pagination\Resolvers\PageInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractResolversTestCase;

final class PageInQueryResolverTest extends AbstractResolversTestCase
{
    /**
     * PageInQueryResolver should resolve pagination data successfully with custom config.
     *
     * @return void
     */
    public function testCustomConfigResolveSuccessfully(): void
    {
        $config = $this->createPagePaginationConfig('page', null, 'perPage');
        $data = (new PageInQueryResolver($config))->resolve($this->createServerRequest([
            'page' => 5,
            'perPage' => 100
        ]));

        self::assertEquals(5, $data->getPageNumber());
        self::assertEquals(100, $data->getPageSize());
    }

    /**
     * PageInQueryResolver should return data with defaults if query attribute not set.
     *
     * @return void
     */
    public function testDefaultWhenQueryAttrNotSet(): void
    {
        $config = $this->createPagePaginationConfig();
        $data = (new PageInQueryResolver($config))->resolve($this->createServerRequest());

        self::assertEquals($config->getNumberDefault(), $data->getPageNumber());
        self::assertEquals($config->getSizeDefault(), $data->getPageSize());
    }
}