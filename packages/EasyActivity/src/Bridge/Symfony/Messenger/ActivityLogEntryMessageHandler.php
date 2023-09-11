<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Messenger;

use EonX\EasyActivity\Interfaces\StoreInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ActivityLogEntryMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private StoreInterface $store,
    ) {
    }

    public function __invoke(ActivityLogEntryMessage $message): void
    {
        $this->store->store($message->getLogEntry());
    }
}
