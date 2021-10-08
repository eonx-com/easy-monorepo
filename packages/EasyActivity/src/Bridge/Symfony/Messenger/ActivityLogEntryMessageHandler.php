<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Messenger;

use EonX\EasyActivity\Interfaces\StoreInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ActivityLogEntryMessageHandler implements MessageHandlerInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\StoreInterface
     */
    private $store;

    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }

    public function __invoke(ActivityLogEntryMessage $message): void
    {
        $this->store->store($message->getLogEntry());
    }
}
