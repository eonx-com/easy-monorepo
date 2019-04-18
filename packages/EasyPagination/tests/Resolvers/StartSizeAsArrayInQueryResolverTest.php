<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Tests\Resolvers;

use StepTheFkUp\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use StepTheFkUp\EasyPagination\Tests\AbstractTestCase;

final class StartSizeAsArrayInQueryResolverTest extends AbstractTestCase
{
    /**
     * StartSizeAsArrayInQueryResolver should resolve pagination data successfully with custom config.
     *
     * @return void
     */
    public function testCustomConfigResolveSuccessfully(): void
    {
        $config = $this->createConfig('page', null, 'perPage');
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => [
                'page' => 5,
                'perPage' => 100
            ]
        ]));

        self::assertEquals(5, $data->getStart());
        self::assertEquals(100, $data->getSize());
    }

    /**
     * StartSizeAsArrayInQueryResolver should resolve pagination data successfully with string values.
     *
     * @return void
     */
    public function testCustomConfigResolveWithStringAsValuesSuccessfully(): void
    {
        $config = $this->createConfig('page', null, 'perPage');
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => [
                'page' => '10',
                'perPage' => '50'
            ]
        ]));

        self::assertEquals(10, $data->getStart());
        self::assertEquals(50, $data->getSize());
    }

    /**
     * StartSizeAsArrayInQueryResolver should return data with defaults if query attribute not an array.
     *
     * @return void
     */
    public function testDefaultWhenQueryAttrNotArray(): void
    {
        $config = $this->createConfig();
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => 'im-not-an-array'
        ]));

        self::assertEquals($config->getStartDefault(), $data->getStart());
        self::assertEquals($config->getSizeDefault(), $data->getSize());
    }

    /**
     * StartSizeAsArrayInQueryResolver should return data with defaults if query attribute not set.
     *
     * @return void
     */
    public function testDefaultWhenQueryAttrNotSet(): void
    {
        $config = $this->createConfig();
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest());

        self::assertEquals($config->getStartDefault(), $data->getStart());
        self::assertEquals($config->getSizeDefault(), $data->getSize());
    }
}

\class_alias(
    StartSizeAsArrayInQueryResolverTest::class,
    'LoyaltyCorp\EasyPagination\Tests\Resolvers\StartSizeAsArrayInQueryResolverTest',
    false
);
