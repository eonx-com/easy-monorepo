<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\Enum;

enum ConfigTag: string
{
    case HandlerConfigProvider = 'easy_logging.handler_config_provider';

    case LoggerConfigurator = 'easy_logging.logger_configurator';

    case ProcessorConfigProvider = 'easy_logging.processor_config_provider';
}
