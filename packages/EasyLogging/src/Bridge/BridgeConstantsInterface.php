<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge;

interface BridgeConstantsInterface
{
    public const KEY_CHANNEL = 'easy_logging_channel';

    public const PARAM_DEFAULT_CHANNEL = 'easy_logging.default_channel';

    public const PARAM_SENSITIVE_DATA_SANITIZER_ENABLED = 'easy_logging.sensitive_data_sanitizer_enabled';

    public const PARAM_STREAM_HANDLER = 'easy_logging.stream_handler';

    public const PARAM_STREAM_HANDLER_LEVEL = 'easy_logging.stream_handler_level';

    public const PARAM_LOGGER_CLASS = 'easy_logging.logger_class';

    public const TAG_HANDLER_CONFIG_PROVIDER = 'easy_logging.handler_config_provider';

    public const TAG_LOGGER_CONFIGURATOR = 'easy_logging.logger_configurator';

    public const TAG_PROCESSOR_CONFIG_PROVIDER = 'easy_logging.processor_config_provider';
}
