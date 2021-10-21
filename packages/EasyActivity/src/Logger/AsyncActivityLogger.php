<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Logger;

use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActivityLoggerInterface;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;

final class AsyncActivityLogger implements ActivityLoggerInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface
     */
    private $activityLogEntryFactory;

    /**
     * @var \EonX\EasyActivity\Interfaces\AsyncDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        ActivityLogEntryFactoryInterface $activityLogEntryFactory,
        AsyncDispatcherInterface $dispatcher
    ) {
        $this->activityLogEntryFactory = $activityLogEntryFactory;
        $this->dispatcher = $dispatcher;
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
