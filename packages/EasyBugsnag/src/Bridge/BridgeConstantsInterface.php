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
    public const TAG_CLIENT_CONFIGURATOR = 'easy_bugsnag.client_configurator';
}
