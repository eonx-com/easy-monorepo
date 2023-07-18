<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel;

use EonX\EasyRandom\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final class EasyRandomServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-random.php' => \base_path('config/easy-random.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-random.php', 'easy-random');

        $this->app->singleton(
            RandomGeneratorInterface::class,
            static function (Container $app): RandomGeneratorInterface {
                $generator = new RandomGenerator();
                $fromConfig = \config('easy-random.uuid_v4_generator');
                $uuidV4Generator = null;

                // UUID v4 from config
                if ($fromConfig !== null) {
                    $uuidV4Generator = $app->make($fromConfig);
                }

                // Fallback using ramsey/uuid if it exists
                if ($uuidV4Generator === null && \class_exists(RamseyUuid::class)) {
                    $uuidV4Generator = new RamseyUuidV4Generator();
                }

                // Fallback using symfony/uid if it exists
                if ($uuidV4Generator === null && \class_exists(SymfonyUuid::class)) {
                    $uuidV4Generator = new SymfonyUidUuidV4Generator();
                }

                if ($uuidV4Generator !== null) {
                    $generator->setUuidV4Generator($uuidV4Generator);
                }

                return $generator;
            }
        );
    }
}
