<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Laravel;

use Illuminate\Support\ServiceProvider;
use LogicException;

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

        $repositories = (array)\config('easy-repository.repositories', []);
        if (\count($repositories) === 0) {
            throw new LogicException(
                'No repositories to register. Please make sure your application has the expected configuration'
            );
        }

        foreach ($repositories as $interface => $repository) {
            $this->app->bind($interface, $repository);
        }
    }
}
