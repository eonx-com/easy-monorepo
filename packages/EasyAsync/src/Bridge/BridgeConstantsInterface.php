<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const LOG_CHANNEL = 'async';

    /**
     * @var string
     */
    public const PARAM_BATCH_DEFAULT_CLASS = 'easy_async.batch_default_class';

    /**
     * @var string
     */
    public const PARAM_BATCHES_TABLE = 'easy_async.batches_table';

    /**
     * @var string
     */
    public const PARAM_BATCH_MESSENGER_BUSES = 'easy_async.messenger_buses';

    /**
     * @var string
     */
    public const PARAM_MESSENGER_WORKER_STOP_MIN_MESSAGES = 'easy_async.messenger_worker_stop_min_messages';

    /**
     * @var string
     */
    public const PARAM_MESSENGER_WORKER_STOP_MAX_MESSAGES = 'easy_async.messenger_worker_stop_max_messages';

    /**
     * @var string
     */
    public const PARAM_MESSENGER_WORKER_STOP_MIN_TIME = 'easy_async.messenger_worker_stop_min_time';

    /**
     * @var string
     */
    public const PARAM_MESSENGER_WORKER_STOP_MAX_TIME = 'easy_async.messenger_worker_stop_max_time';

    /**
     * @var string
     */
    public const SERVICE_LOGGER = 'easy_async.logger';
}
