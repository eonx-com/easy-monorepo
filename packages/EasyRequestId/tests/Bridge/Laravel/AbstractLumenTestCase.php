<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Bridge\Laravel;

use EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRequestId\Bridge\Laravel\EasyRequestIdServiceProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;
    
    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->register(EasyRequestIdServiceProvider::class);
        $app->register(EasyRandomServiceProvider::class);

        $app->extend(
            RandomGeneratorInterface::class,
            static function (RandomGeneratorInterface $random): RandomGeneratorInterface {
                return $random->setUuidV4Generator(new RamseyUuidV4Generator());
            }
        );

        return $this->app = $app;
    }
}
