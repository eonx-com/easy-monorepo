<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Symfony\Config\EasyLockConfig;

return static function (EasyLockConfig $easyLockConfig, ContainerConfigurator $containerConfigurator): void {
    $easyLockConfig->connection('in_memory_connection');

    $services = $containerConfigurator->services();

    $services->set('in_memory_connection', Connection::class)
        ->factory([DriverManager::class, 'getConnection'])
        ->arg('$params', [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
};
