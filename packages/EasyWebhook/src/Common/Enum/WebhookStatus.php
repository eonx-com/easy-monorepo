<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Enum;

enum WebhookStatus: string
{
    case Failed = 'failed';

    case FailedPendingRetry = 'failed_pending_retry';

    case Pending = 'pending';

    case Success = 'success';
}
