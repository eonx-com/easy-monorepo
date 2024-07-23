<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Logger;

use EonX\EasyActivity\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\Factory\ActivityLogEntryFactoryInterface;

final readonly class AsyncActivityLogger implements ActivityLoggerInterface
{
    public function __construct(
        private ActivityLogEntryFactoryInterface $activityLogEntryFactory,
        private AsyncDispatcherInterface $dispatcher,
    ) {
    }

    public function addActivityLogEntry(ActivityAction $action, object $object, array $changeSet): void
    {
        $logEntry = $this->activityLogEntryFactory->create($action, $object, $changeSet);

        if ($logEntry !== null) {
            $this->dispatcher->dispatch($logEntry);
        }
    }
}
