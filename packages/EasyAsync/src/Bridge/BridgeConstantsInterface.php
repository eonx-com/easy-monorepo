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
    public const PARAM_BATCH_ITEMS_TABLE = 'easy_async.batch_items_table';

    /**
     * @var string
     */
    public const PARAM_BATCH_MESSENGER_BUSES = 'easy_async.messenger_buses';

    /**
     * @var string
     */
    public const SERVICE_LOGGER = 'easy_async.logger';
}
