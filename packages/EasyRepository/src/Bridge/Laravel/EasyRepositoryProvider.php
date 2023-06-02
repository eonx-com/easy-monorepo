<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Bridge\Laravel;

use EonX\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException;
use Illuminate\Support\ServiceProvider;

final class EasyRepositoryProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-repository.php' => \base_path('config/easy-repository.php'),
        ]);
    }

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
