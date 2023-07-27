<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Logger;

use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActivityLoggerInterface;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;

final class AsyncActivityLogger implements ActivityLoggerInterface
{
    public function __construct(
        private ActivityLogEntryFactoryInterface $activityLogEntryFactory,
        private AsyncDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function addActivityLogEntry(string $action, object $object, array $changeSet): void
    {
        $logEntry = $this->activityLogEntryFactory->create($action, $object, $changeSet);

        if ($logEntry !== null) {
            $this->dispatcher->dispatch($logEntry);
        }
    }
}
