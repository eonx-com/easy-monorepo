<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use Illuminate\Support\ServiceProvider;

final class EasyRandomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RandomGeneratorInterface::class, static function (): RandomGeneratorInterface {
            return new RandomGenerator();
        });
    }
}
