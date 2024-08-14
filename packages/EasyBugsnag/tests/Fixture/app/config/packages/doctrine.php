<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrineConfig): void {
    $dbal = $doctrineConfig->dbal();

    $connection = $dbal->connection('default');

    $connection
        ->driver('pdo_sqlite')
        ->url('sqlite:///:memory:');

    $doctrineConfig->orm()
        ->autoGenerateProxyClasses(true);

    $entityManager = $doctrineConfig->orm()
        ->entityManager('default');

    $entityManager
        ->namingStrategy('doctrine.orm.naming_strategy.underscore_number_aware');

    $entityManager->mapping('AppEntity')
        ->dir(param('kernel.project_dir') . '/src/Entity')
        ->isBundle(false)
        ->prefix('EonX\EasyBugsnag\Tests\Fixture\App\Entity')
        ->type('attribute');
};
