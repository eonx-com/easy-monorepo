<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Resolvers;

use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use EonX\EasyPagination\Tests\AbstractTestCase;

final class StartSizeAsArrayInQueryResolverTest extends AbstractTestCase
{
    public function testCustomConfigResolveSuccessfully(): void
    {
        $config = $this->createConfig('page', null, 'perPage');
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => [
                'page' => 5,
                'perPage' => 100,
            ],
        ]));

        self::assertEquals(5, $data->getStart());
        self::assertEquals(100, $data->getSize());
    }

    public function testCustomConfigResolveWithStringAsValuesSuccessfully(): void
    {
        $config = $this->createConfig('page', null, 'perPage');
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => [
                'page' => '10',
                'perPage' => '50',
            ],
        ]));

        self::assertEquals(10, $data->getStart());
        self::assertEquals(50, $data->getSize());
    }

    public function testDefaultWhenQueryAttrNotArray(): void
    {
        $config = $this->createConfig();
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest([
            'page' => 'im-not-an-array',
        ]));

        self::assertEquals($config->getStartDefault(), $data->getStart());
        self::assertEquals($config->getSizeDefault(), $data->getSize());
    }

    public function testDefaultWhenQueryAttrNotSet(): void
    {
        $config = $this->createConfig();
        $data = (new StartSizeAsArrayInQueryResolver($config, 'page'))->resolve($this->createServerRequest());

        self::assertEquals($config->getStartDefault(), $data->getStart());
        self::assertEquals($config->getSizeDefault(), $data->getSize());
    }
}
