<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_DEFAULT_CHANNEL = 'easy_logging.default_channel';

    /**
     * @var string
     */
    public const TAG_HANDLER_CONFIG_PROVIDER = 'easy_logging.handler_config_provider';

    /**
     * @var string
     */
    public const TAG_LOGGER_CONFIGURATOR = 'easy_logging.logger_configurator';

    /**
     * @var string
     */
    public const TAG_PROCESSOR_CONFIG_PROVIDER = 'easy_logging.processor_config_provider';
}
