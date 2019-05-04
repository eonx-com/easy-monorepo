<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPsr7Factory\Tests\Bridge\Laravel;

use LoyaltyCorp\EasyPsr7Factory\Bridge\Laravel\EasyPsr7FactoryServiceProvider;
use LoyaltyCorp\EasyPsr7Factory\EasyPsr7Factory;
use LoyaltyCorp\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use LoyaltyCorp\EasyPsr7Factory\Tests\AbstractTestCase;

final class EasyPsr7FactoryServiceProviderTest extends AbstractTestCase
{
    /**
     * Provider should register the expected services.
     *
     * @return void
     */
    public function testRegisterExpectedServices(): void
    {
        $app = new \Laravel\Lumen\Application(__DIR__);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyPsr7FactoryServiceProvider($app))->register();

        self::assertInstanceOf(EasyPsr7Factory::class, $app->get(EasyPsr7FactoryInterface::class));
    }
}

\class_alias(
    EasyPsr7FactoryServiceProviderTest::class,
    'StepTheFkUp\EasyPsr7Factory\Tests\Bridge\Laravel\EasyPsr7FactoryServiceProviderTest',
    false
);
