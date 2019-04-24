<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPsr7Factory\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use StepTheFkUp\EasyPsr7Factory\EasyPsr7Factory;
use StepTheFkUp\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;

final class EasyPsr7FactoryServiceProvider extends ServiceProvider
{
    /**
     * Register EasyEasyPsr7Factory service.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(EasyPsr7FactoryInterface::class, EasyPsr7Factory::class);
    }
}

\class_alias(
    EasyPsr7FactoryServiceProvider::class,
    'LoyaltyCorp\EasyPsr7Factory\Bridge\Laravel\EasyPsr7FactoryServiceProvider',
    false
);
