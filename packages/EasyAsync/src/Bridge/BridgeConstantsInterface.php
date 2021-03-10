<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge;

interface BridgeConstantsInterface
{
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
}
