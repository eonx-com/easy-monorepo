<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException;

final class EasyRepositoryProvider extends ServiceProvider
{
    /**
     * Publish configuration file.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-repository.php' => \base_path('config/easy-repository.php')
        ]);
    }

    /**
     * Register repositories into the services container.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-repository.php', 'easy-repository');

        $repositories = \config('easy-repository.repositories', []);
        if (empty($repositories)) {
            throw new EmptyRepositoriesListException(
                'No repositories to register. Please make sure your application has the expected configuration'
            );
        }

        foreach ($repositories as $interface => $repository) {
            $this->app->bind($interface, $repository);
        }
    }
}

\class_alias(
    EasyRepositoryProvider::class,
    'StepTheFkUp\EasyRepository\Bridge\Laravel\EasyRepositoryProvider',
    false
);
