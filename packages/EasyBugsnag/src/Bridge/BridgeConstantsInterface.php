<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_API_KEY = 'easy_bugsnag.api_key';

    /**
     * @var string
     */
    public const PARAM_DOCTRINE_DBAL_CONNECTIONS = 'easy_bugsnag.doctrine_dbal.connections';

    /**
     * @var string
     */
    public const PARAM_DOCTRINE_DBAL_ENABLED = 'easy_bugsnag.doctrine_dbal.enabled';

    /**
     * @var string
     */
    public const PARAM_SESSION_TRACKING_EXCLUDE = 'easy_bugsnag.session_tracking_exclude';

    /**
     * @var string
     */
    public const PARAM_SESSION_TRACKING_EXCLUDE_DELIMITER = 'easy_bugsnag.session_tracking_exclude_delimiter';

    /**
     * @var string
     */
    public const TAG_CLIENT_CONFIGURATOR = 'easy_bugsnag.client_configurator';
}
