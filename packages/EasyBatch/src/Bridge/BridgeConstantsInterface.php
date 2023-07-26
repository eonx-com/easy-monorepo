<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_BATCH_CLASS = 'easy_batch.batch.class';

    public const PARAM_BATCH_ITEM_CLASS = 'easy_batch.batch_item.class';

    public const PARAM_BATCH_ITEM_PER_PAGE = 'easy_batch.batch_item.per_page';

    public const PARAM_BATCH_ITEM_TABLE = 'easy_batch.batch_item.table';

    public const PARAM_BATCH_TABLE = 'easy_batch.batch.table';

    public const PARAM_DATE_TIME_FORMAT = 'easy_batch.date_time.format';

    public const PARAM_LOCK_TTL = 'easy_batch.lock.ttl';

    public const SERVICE_BATCH_ID_STRATEGY = 'easy_batch.batch.id_strategy';

    public const SERVICE_BATCH_ITEM_ID_STRATEGY = 'easy_batch.batch_item.id_strategy';

    public const SERVICE_BATCH_ITEM_TRANSFORMER = 'easy_batch.batch_item.transformer';

    public const SERVICE_BATCH_MESSAGE_SERIALIZER = 'easy_batch.batch.message_serializer';

    public const SERVICE_BATCH_TRANSFORMER = 'easy_batch.batch.transformer';
}
