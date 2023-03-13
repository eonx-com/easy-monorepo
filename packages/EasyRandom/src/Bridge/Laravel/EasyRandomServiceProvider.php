<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel;

use EonX\EasyRandom\Enums\UuidVersion;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidV6GeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\UuidV6\RamseyUuidV6Generator;
use EonX\EasyRandom\UuidV6\SymfonyUidUuidV6Generator;
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
            function (Container $app): RandomGeneratorInterface {
                $defaultUuidVersion = UuidVersion::from(\config('easy-random.default_uuid_version'));
                $uuidV4Generator = $this->resolveUuidV4Generator($app);
                $uuidV6Generator = $this->resolveUuidV6Generator($app);

                return new RandomGenerator($defaultUuidVersion, $uuidV4Generator, $uuidV6Generator);
            }
        );
    }

    private function resolveUuidV4Generator(Container $app): ?UuidV4GeneratorInterface
    {
        $uuidV4GeneratorFromConfig = \config('easy-random.uuid_v4_generator');
        $uuidV4Generator = null;

        if ($uuidV4GeneratorFromConfig !== null) {
            $uuidV4Generator = $app->make($uuidV4GeneratorFromConfig);
        }

        if ($uuidV4Generator === null && \class_exists(RamseyUuid::class)) {
            $uuidV4Generator = new RamseyUuidV4Generator();
        }

        if ($uuidV4Generator === null && \class_exists(SymfonyUuid::class)) {
            $uuidV4Generator = new SymfonyUidUuidV4Generator();
        }

        return $uuidV4Generator;
    }

    private function resolveUuidV6Generator(Container $app): ?UuidV6GeneratorInterface
    {
        $uuidV6GeneratorFromConfig = \config('easy-random.uuid_v6_generator');
        $uuidV6Generator = null;

        if ($uuidV6GeneratorFromConfig !== null) {
            $uuidV6Generator = $app->make($uuidV6GeneratorFromConfig);
        }

        if ($uuidV6Generator === null && \class_exists(RamseyUuid::class)) {
            $uuidV6Generator = new RamseyUuidV6Generator();
        }

        if ($uuidV6Generator === null && \class_exists(SymfonyUuid::class)) {
            $uuidV6Generator = new SymfonyUidUuidV6Generator();
        }

        return $uuidV6Generator;
    }
}
