<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_lock', [
        'connection' => 'in_memory_connection',
    ]);

    $services = $containerConfigurator->services();

    $services->set('in_memory_connection', Connection::class)
        ->factory(DriverManager::getConnection(...))
        ->arg('$params', [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
};
