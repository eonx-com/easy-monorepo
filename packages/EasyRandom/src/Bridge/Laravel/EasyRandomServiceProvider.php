<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel;

use EonX\EasyRandom\Bridge\Laravel\Exceptions\UnsupportedUuidVersionException;
use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV6Generator;
use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV4Generator;
use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
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

        $uuidVersion = (int)\config('easy-random.uuid_version', 6);

        if (\in_array($uuidVersion, [4, 6], true) === false) {
            throw UnsupportedUuidVersionException::create($uuidVersion);
        }

        $this->app->singleton(RandomStringGeneratorInterface::class, RandomStringGenerator::class);
        $this->app->singleton(RandomIntegerGeneratorInterface::class, RandomIntegerGenerator::class);

        if ($uuidVersion === 4) {
            if (\class_exists(SymfonyUuid::class)) {
                $this->app->singleton(UuidGeneratorInterface::class, SymfonyUuidV4Generator::class);
            }

            if (\class_exists(RamseyUuid::class)) {
                $this->app->singleton(UuidGeneratorInterface::class, RamseyUuidV4Generator::class);
            }
        }

        if ($uuidVersion === 6) {
            if (\class_exists(SymfonyUuid::class)) {
                $this->app->singleton(UuidGeneratorInterface::class, SymfonyUuidV6Generator::class);
            }

            if (\class_exists(RamseyUuid::class)) {
                $this->app->singleton(UuidGeneratorInterface::class, RamseyUuidV6Generator::class);
            }
        }

        $this->app->singleton(RandomGeneratorInterface::class, RandomGenerator::class);
    }
}
