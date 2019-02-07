<?php
declare(strict_types=1);

namespace StepTheFkUp\Psr7Factory\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use StepTheFkUp\Psr7Factory\Interfaces\Psr7FactoryInterface;
use StepTheFkUp\Psr7Factory\Psr7Factory;

final class Psr7FactoryServiceProvider extends ServiceProvider
{
    /**
     * Register Psr7Factory service.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(Psr7FactoryInterface::class, Psr7Factory::class);
    }
}
