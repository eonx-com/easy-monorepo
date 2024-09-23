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

    $entityManager->mapping('AppAdvancedSearchFilterApiResource')
        ->dir(param('kernel.project_dir') . '/src/AdvancedSearchFilter/ApiResource')
        ->isBundle(false)
        ->prefix('EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource')
        ->type('attribute');

    $entityManager->mapping('AppCustomPaginatorApiResource')
        ->dir(param('kernel.project_dir') . '/src/CustomPaginator/ApiResource')
        ->isBundle(false)
        ->prefix('EonX\EasyApiPlatform\Tests\Fixture\App\CustomPaginator\ApiResource')
        ->type('attribute');

    $entityManager->mapping('AppReturnNotFoundOnReadOperationApiResource')
        ->dir(param('kernel.project_dir') . '/src/ReturnNotFoundOnReadOperation/ApiResource')
        ->isBundle(false)
        ->prefix('EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\ApiResource')
        ->type('attribute');
};
