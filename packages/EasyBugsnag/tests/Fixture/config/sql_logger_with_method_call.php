<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Configuration;
use EonX\EasyBugsnag\Tests\Stub\Driver\ConnectionStub;
use EonX\EasyBugsnag\Tests\Stub\Logging\SqlLoggerStub;
use Symfony\Config\EasyBugsnagConfig;

return static function (EasyBugsnagConfig $easyBugsnagConfig, ContainerConfigurator $containerConfigurator): void {
    $easyBugsnagConfig->apiKey('my-api-key');

    $easyBugsnagConfig->doctrineDbal()
        ->enabled(true);

    $services = $containerConfigurator->services();

    $services->set('sql_logger_stub')
        ->class(SqlLoggerStub::class);

    $services->set('doctrine.dbal.default_connection.configuration')
        ->class(Configuration::class)
        ->call('setSQLLogger', [service('sql_logger_stub')]);

    $services->set('doctrine.dbal.default_connection')
        ->class(ConnectionStub::class);
};
