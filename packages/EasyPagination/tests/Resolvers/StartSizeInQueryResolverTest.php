<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Resolvers;

use EonX\EasyPagination\Resolvers\StartSizeInQueryResolver;
use EonX\EasyPagination\Tests\AbstractTestCase;

final class StartSizeInQueryResolverTest extends AbstractTestCase
{
    public function testCustomConfigResolveSuccessfully(): void
    {
        $config = $this->createConfig('page', null, 'perPage');
        $data = (new StartSizeInQueryResolver($config))->resolve($this->createServerRequest([
            'page' => 5,
            'perPage' => 100,
        ]));

        self::assertEquals(5, $data->getStart());
        self::assertEquals(100, $data->getSize());
    }

    public function testCustomConfigResolveWithStringAsValuesSuccessfully(): void
    {
        $config = $this->createConfig('page', null, 'perPage');
        $data = (new StartSizeInQueryResolver($config))->resolve($this->createServerRequest([
            'page' => '10',
            'perPage' => '50',
        ]));

        self::assertEquals(10, $data->getStart());
        self::assertEquals(50, $data->getSize());
    }

    public function testDefaultWhenQueryAttrNotSet(): void
    {
        $config = $this->createConfig();
        $data = (new StartSizeInQueryResolver($config))->resolve($this->createServerRequest());

        self::assertEquals($config->getStartDefault(), $data->getStart());
        self::assertEquals($config->getSizeDefault(), $data->getSize());
    }
}
