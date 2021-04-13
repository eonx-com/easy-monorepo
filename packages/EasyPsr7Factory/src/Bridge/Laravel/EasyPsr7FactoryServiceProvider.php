<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Bridge\Laravel;

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;

final class EasyPsr7FactoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EasyPsr7FactoryInterface::class, EasyPsr7Factory::class);

        // Not singleton on purpose
        $this->app->bind(ServerRequestInterface::class, static function (Container $app): ServerRequestInterface {
            return $app->make(EasyPsr7FactoryInterface::class)->createRequest($app->make(Request::class));
        });
    }
}
