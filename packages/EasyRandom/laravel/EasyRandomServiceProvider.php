<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Laravel;

use EonX\EasyRandom\Generator\RamseyUuidV4Generator;
use EonX\EasyRandom\Generator\RamseyUuidV6Generator;
use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\SymfonyUuidV4Generator;
use EonX\EasyRandom\Generator\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use Illuminate\Support\ServiceProvider;
use LogicException;
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
            throw new LogicException(\sprintf('Unsupported UUID version "%d".', $uuidVersion));
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
