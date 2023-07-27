<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Messenger;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncDispatcher implements AsyncDispatcherInterface
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    public function dispatch(ActivityLogEntry $activityLogEntry): void
    {
        $this->bus->dispatch(new ActivityLogEntryMessage($activityLogEntry));
    }
}
