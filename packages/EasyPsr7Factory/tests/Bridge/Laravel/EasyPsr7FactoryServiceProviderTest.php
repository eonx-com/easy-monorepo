<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Laravel;

use EonX\EasyPsr7Factory\Bridge\Laravel\EasyPsr7FactoryServiceProvider;
use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use EonX\EasyPsr7Factory\Tests\AbstractTestCase;

final class EasyPsr7FactoryServiceProviderTest extends AbstractTestCase
{
    public function testRegisterExpectedServices(): void
    {
        $app = new \Laravel\Lumen\Application(__DIR__);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyPsr7FactoryServiceProvider($app))->register();

        self::assertInstanceOf(EasyPsr7Factory::class, $app->get(EasyPsr7FactoryInterface::class));
    }
}
