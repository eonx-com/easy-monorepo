<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge;

use EonX\EasyBatch\Listeners\BatchObjectApprovedListener;
use EonX\EasyBatch\Bridge\Symfony\Listeners\BatchItemAttemptsAndIdListener;
use EonX\EasyBatch\Bridge\Symfony\Listeners\BatchItemFailedAttemptsAndIdListener;
use EonX\EasyBatch\Bridge\Symfony\Listeners\BatchItemFailedClassListener;
use EonX\EasyBatch\Bridge\Symfony\Listeners\BatchItemFailedRequiresApprovalListener;
use EonX\EasyBatch\Bridge\Symfony\Listeners\BatchItemRequiresApprovalListener;
use EonX\EasyBatch\Listeners\ChildBatchCompletedListener;

interface BridgeConstantsInterface
{
    /**
     * @var string[]
     */
    public const LISTENERS = [
        BatchObjectApprovedListener::class,
        BatchItemAttemptsAndIdListener::class,
        BatchItemFailedAttemptsAndIdListener::class,
        BatchItemFailedClassListener::class,
        BatchItemFailedRequiresApprovalListener::class,
        BatchItemRequiresApprovalListener::class,
        ChildBatchCompletedListener::class,
    ];

    /**
     * @var string
     */
    public const PARAM_BATCH_CLASS = 'easy_batch.batch.class';

    /**
     * @var string
     */
    public const PARAM_BATCH_TABLE = 'easy_batch.batch.table';

    /**
     * @var string
     */
    public const PARAM_BATCH_ITEM_CLASS = 'easy_batch.batch_item.class';

    /**
     * @var string
     */
    public const PARAM_BATCH_ITEM_TABLE = 'easy_batch.batch_item.table';

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
}
