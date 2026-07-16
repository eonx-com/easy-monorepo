<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Enum;

use EonX\EasyUtils\Common\Enum\EnumTrait;

enum BatchObjectStatus: string
{
    use EnumTrait;

    case BatchPendingApproval = 'batch_pending_approval';

    case Cancelled = 'cancelled';

    case Created = 'created';

    case Failed = 'failed';

    case FailedPendingRetry = 'failed_pending_retry';

    case Pending = 'pending';

    case Processing = 'processing';

    case ProcessingDependentObjects = 'processing_dependent_objects';

    case Succeeded = 'succeeded';

    case SucceededPendingApproval = 'succeeded_pending_approval';

    public const array STATUSES_FOR_CANCEL = [
        self::Cancelled,
        self::Failed,
    ];

    public const array STATUSES_FOR_COMPLETE = [
        self::Cancelled,
        self::Failed,
        self::Succeeded,
    ];

    public const array STATUSES_FOR_DISPATCH = [
        self::BatchPendingApproval,
        self::Created,
        self::FailedPendingRetry,
    ];

    public const array STATUSES_FOR_PENDING_APPROVAL = [
        self::BatchPendingApproval,
        self::ProcessingDependentObjects,
    ];
}
