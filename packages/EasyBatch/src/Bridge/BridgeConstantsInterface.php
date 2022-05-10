<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_BATCH_CLASS = 'easy_batch.batch.class';

    /**
     * @var string
     */
    public const PARAM_BATCH_ITEM_CLASS = 'easy_batch.batch_item.class';

    /**
     * @var string
     */
    public const PARAM_BATCH_ITEM_PER_PAGE = 'easy_batch.batch_item.per_page';

    /**
     * @var string
     */
    public const PARAM_BATCH_ITEM_TABLE = 'easy_batch.batch_item.table';

    /**
     * @var string
     */
    public const PARAM_BATCH_TABLE = 'easy_batch.batch.table';

    /**
     * @var string
     */
    public const PARAM_DATE_TIME_FORMAT = 'easy_batch.date_time.format';

    /**
     * @var string
     */
    public const SERVICE_BATCH_ID_STRATEGY = 'easy_batch.batch.id_strategy';

    /**
     * @var string
     */
    public const SERVICE_BATCH_ITEM_ID_STRATEGY = 'easy_batch.batch_item.id_strategy';

    /**
     * @var string
     */
    public const SERVICE_BATCH_ITEM_TRANSFORMER = 'easy_batch.batch_item.transformer';

    /**
     * @var string
     */
    public const SERVICE_BATCH_MESSAGE_SERIALIZER = 'easy_batch.batch.message_serializer';

    /**
     * @var string
     */
    public const SERVICE_BATCH_TRANSFORMER = 'easy_batch.batch.transformer';
}
