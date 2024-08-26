<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Logger;

use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\Factory\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Common\Store\StoreInterface;

final readonly class SyncActivityLogger implements ActivityLoggerInterface
{
    public function __construct(
        private ActivityLogEntryFactoryInterface $activityLogEntryFactory,
        private StoreInterface $store,
    ) {
    }

    public function addActivityLogEntry(ActivityAction|string $action, object $object, array $changeSet): void
    {
        $logEntry = $this->activityLogEntryFactory->create($action, $object, $changeSet);

        if ($logEntry !== null) {
            $this->store->store($logEntry);
        }
    }
}
