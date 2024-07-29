<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Enum;

use EonX\EasyUtils\Common\Enum\EnumTrait;

enum BatchObjectStatus: string
{
    use EnumTrait;

    case BatchPendingApproval = 'batch_pending_approval';

    case FailedPendingRetry = 'failed_pending_retry';

    case ProcessingDependentObjects = 'processing_dependent_objects';

    case Created = 'created';

    case Pending = 'pending';

    public const STATUSES_FOR_CANCEL = [
        self::Cancelled,
        self::Failed,
    ];

    public const STATUSES_FOR_COMPLETE = [
        self::Cancelled,
        self::Failed,
        self::Succeeded,
    ];

    public const STATUSES_FOR_DISPATCH = [
        self::BatchPendingApproval,
        self::Created,
        self::FailedPendingRetry,
    ];

    case Cancelled = 'cancelled';

    case Failed = 'failed';

    case Processing = 'processing';

    case Succeeded = 'succeeded';

    case SucceededPendingApproval = 'succeeded_pending_approval';

    public const STATUSES_FOR_PENDING_APPROVAL = [
        self::BatchPendingApproval,
        self::ProcessingDependentObjects,
    ];
}
