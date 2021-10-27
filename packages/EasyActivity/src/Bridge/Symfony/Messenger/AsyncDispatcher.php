<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Messenger;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncDispatcher implements AsyncDispatcherInterface
{
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(ActivityLogEntry $activityLogEntry): void
    {
        $this->bus->dispatch(new ActivityLogEntryMessage($activityLogEntry));
    }
}
