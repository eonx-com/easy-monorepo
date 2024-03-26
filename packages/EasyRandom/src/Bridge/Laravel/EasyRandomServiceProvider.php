<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel;

use EonX\EasyRandom\Bridge\Laravel\Exceptions\UnsupportedUuidVersionException;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Generators\UuidGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Illuminate\Support\ServiceProvider;
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
            throw UnsupportedUuidVersionException::create($uuidVersion, $supportedUuidVersions);
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
