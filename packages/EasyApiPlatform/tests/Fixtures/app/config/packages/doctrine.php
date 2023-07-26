<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrineConfig, ContainerConfigurator $containerConfigurator): void {
    $dbal = $doctrineConfig->dbal();

    $connection = $dbal->connection('default');

    $connection->driver('pdo_sqlite')
        ->url('sqlite:///:memory:');

    $doctrineConfig->orm()
        ->autoGenerateProxyClasses(true);

    $entityManager = $doctrineConfig->orm()
        ->entityManager('default');

    $entityManager->mapping('AppApiResource')
        ->alias('AppApiResource')
        ->dir(\param('kernel.project_dir') . '/src/ApiResource')
        ->isBundle(false)
        ->prefix('EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource')
        ->type('attribute');
};
