<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bundle\Enum\ConfigParam as EasyLoggingConfigParam;
use EonX\EasyTest\Monolog\Logger\LoggerStub;
use Symfony\Config\EasyLoggingConfig;

return static function (EasyLoggingConfig $easyLoggingConfig, ContainerConfigurator $container): void {
    $easyLoggingConfig->streamHandler(false);

    $container->parameters()
        ->set(EasyLoggingConfigParam::LoggerClass->value, LoggerStub::class);
};
