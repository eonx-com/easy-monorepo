<?php
declare(strict_types=1);

namespace StepTheFkUp\Psr7Factory\Tests\Bridge\Laravel;

use StepTheFkUp\Psr7Factory\Bridge\Laravel\Psr7FactoryServiceProvider;
use StepTheFkUp\Psr7Factory\Interfaces\Psr7FactoryInterface;
use StepTheFkUp\Psr7Factory\Psr7Factory;
use StepTheFkUp\Psr7Factory\Tests\AbstractTestCase;

final class Psr7FactoryServiceProviderTest extends AbstractTestCase
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
        (new Psr7FactoryServiceProvider($app))->register();

        self::assertInstanceOf(Psr7Factory::class, $app->get(Psr7FactoryInterface::class));
    }
}
