<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use StepTheFkUp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException;

final class EasyRepositoryProvider extends ServiceProvider
{
    /**
     * Register repositories into the services container.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException
     */
    public function register(): void
    {
        $config = $this->app->get('config')->get('easy-repository');
        $repositories = $config['repositories'] ?? [];

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