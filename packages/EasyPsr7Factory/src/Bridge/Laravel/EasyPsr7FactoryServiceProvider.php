<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Bridge\Laravel;

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Illuminate\Support\ServiceProvider;

final class EasyPsr7FactoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EasyPsr7FactoryInterface::class, EasyPsr7Factory::class);
    }
}
