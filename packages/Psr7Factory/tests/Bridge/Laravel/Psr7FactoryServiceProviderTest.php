<?php
declare(strict_types=1);

namespace StepTheFkUp\Psr7Factory\Tests\Bridge\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Mockery\MockInterface;
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
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->mock(Application::class, function (MockInterface $app): void {
            $app->shouldReceive('bind')->once()->withArgs(function (string $abstract, string $concrete): bool {
                self::assertEquals(Psr7FactoryInterface::class, $abstract);
                self::assertEquals(Psr7Factory::class, $concrete);

                return true;
            });
        });

        (new Psr7FactoryServiceProvider($app))->register();
    }
}
