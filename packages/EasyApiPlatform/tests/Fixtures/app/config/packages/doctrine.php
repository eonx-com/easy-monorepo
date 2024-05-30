<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrineConfig): void {
    $dbal = $doctrineConfig->dbal();

    $connection = $dbal->connection('default');

    $connection->driver('pdo_sqlite')
        ->url('sqlite:///:memory:');

    $doctrineConfig->orm()
        ->autoGenerateProxyClasses(true);

    $entityManager = $doctrineConfig->orm()
        ->entityManager('default');

    $entityManager->mapping('AppCaseAdvancedSearchFilterApiResource')
        ->dir(param('kernel.project_dir') . '/src/Case/AdvancedSearchFilter/ApiResource')
        ->isBundle(false)
        ->prefix('EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\ApiResource')
        ->type('attribute');

    $entityManager->mapping('AppCaseEasyErrorHandlerApiResource')
        ->dir(param('kernel.project_dir') . '/src/Case/EasyErrorHandler/ApiResource')
        ->isBundle(false)
        ->prefix('EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource')
        ->type('attribute');
};
