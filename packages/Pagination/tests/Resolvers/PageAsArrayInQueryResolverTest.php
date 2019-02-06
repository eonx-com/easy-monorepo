<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Resolvers;

use StepTheFkUp\Pagination\Resolvers\PageAsArrayInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractResolversTestCase;

final class PageAsArrayInQueryResolverTest extends AbstractResolversTestCase
{
    /**
     * PageAsArrayInQueryResolver should resolve pagination data successfully with custom config.
     *
     * @return void
     */
    public function testCustomConfigResolveSuccessfully(): void
    {
        $config = $this->createPagePaginationConfig('page', null, 'perPage');
        $data = (new PageAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => [
                'page' => 5,
                'perPage' => 100
            ]
        ]));

        self::assertEquals(5, $data->getPageNumber());
        self::assertEquals(100, $data->getPageSize());
    }

    /**
     * PageAsArrayInQueryResolver should return data with defaults if query attribute not an array.
     *
     * @return void
     */
    public function testDefaultWhenQueryAttrNotArray(): void
    {
        $config = $this->createPagePaginationConfig();
        $data = (new PageAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => 'im-not-an-array'
        ]));

        self::assertEquals($config->getNumberDefault(), $data->getPageNumber());
        self::assertEquals($config->getSizeDefault(), $data->getPageSize());
    }

    /**
     * PageAsArrayInQueryResolver should return data with defaults if query attribute not set.
     *
     * @return void
     */
    public function testDefaultWhenQueryAttrNotSet(): void
    {
        $config = $this->createPagePaginationConfig();
        $data = (new PageAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest());

        self::assertEquals($config->getNumberDefault(), $data->getPageNumber());
        self::assertEquals($config->getSizeDefault(), $data->getPageSize());
    }
}
