<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_REQUEST_LIMITS_MAX = 'easy_swoole.request_limits.max';

    /**
     * @var string
     */
    public const PARAM_REQUEST_LIMITS_MIN = 'easy_swoole.request_limits.min';

    /**
     * @var string
     */
    public const TAG_APP_STATE_CHECKER = 'easy_swoole.app_state_checker';

    /**
     * @var string
     */
    public const TAG_APP_STATE_RESETTER = 'easy_swoole.app_state_resetter';
}
