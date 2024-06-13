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

//    $entityManager = $doctrineConfig->orm()
//        ->entityManager('default');
//
//    $entityManager->mapping('AppCaseAdvancedSearchFilterApiResource')
//        ->dir(param('kernel.project_dir') . '/src/AdvancedSearchFilter/ApiResource')
//        ->isBundle(false)
//        ->prefix('EonX\EasyApiPlatform\Tests\Fixtures\App\AdvancedSearchFilter\ApiResource')
//        ->type('attribute');
//
//    $entityManager->mapping('AppCaseCustomPaginatorApiResource')
//        ->dir(param('kernel.project_dir') . '/src/CustomPaginator/ApiResource')
//        ->isBundle(false)
//        ->prefix('EonX\EasyApiPlatform\Tests\Fixtures\App\CustomPaginator\ApiResource')
//        ->type('attribute');
//
//    $entityManager->mapping('AppCaseReadListenerApiResource')
//        ->dir(param('kernel.project_dir') . '/src/ReadListener/ApiResource')
//        ->isBundle(false)
//        ->prefix('EonX\EasyApiPlatform\Tests\Fixtures\App\ReadListener\ApiResource')
//        ->type('attribute');
};
