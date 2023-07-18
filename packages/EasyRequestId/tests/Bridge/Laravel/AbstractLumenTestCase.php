<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Bridge\Laravel;

use EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRequestId\Bridge\Laravel\EasyRequestIdServiceProvider;
use EonX\EasyRequestId\Tests\AbstractTestCase;
use Illuminate\Bus\BusServiceProvider;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(BusServiceProvider::class);
        $this->app->register(EasyRequestIdServiceProvider::class);
        $this->app->register(EasyRandomServiceProvider::class);

        $this->app->extend(
            RandomGeneratorInterface::class,
            static fn (
                RandomGeneratorInterface $randomGenerator,
            ): RandomGeneratorInterface => $randomGenerator->setUuidV4Generator(new RamseyUuidV4Generator())
        );

        return $this->app;
    }
}
