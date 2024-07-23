<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Laravel;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Symfony\Component\Uid\Factory\UuidFactory;

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
        $supportedUuidVersions = [1, 4, 6, 7];

        if (\in_array($uuidVersion, $supportedUuidVersions, true) === false) {
            throw new LogicException(\sprintf(
                'Unsupported UUID version "%d". Supported versions are: %s',
                $uuidVersion,
                \implode(', ', $supportedUuidVersions)
            ));
        }

        $this->app->singleton(RandomStringGeneratorInterface::class, RandomStringGenerator::class);
        $this->app->singleton(RandomIntegerGeneratorInterface::class, RandomIntegerGenerator::class);
        $this->app->singleton(
            UuidGeneratorInterface::class,
            static fn (): UuidGeneratorInterface => new UuidGenerator(new UuidFactory($uuidVersion))
        );

        $this->app->singleton(RandomGeneratorInterface::class, RandomGenerator::class);
    }
}
