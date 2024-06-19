<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Messenger\Dispatcher;

use EonX\EasyActivity\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyActivity\Common\Entity\ActivityLogEntry;
use EonX\EasyActivity\Messenger\Message\ActivityLogEntryMessage;
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
