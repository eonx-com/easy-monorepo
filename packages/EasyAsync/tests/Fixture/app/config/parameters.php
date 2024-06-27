<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bundle\Enum\ConfigParam as EasyLoggingConfigParam;
use EonX\EasyTest\Monolog\Logger\LoggerStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(EasyLoggingConfigParam::LoggerClass->value, LoggerStub::class);
};
