<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Messenger\MessageHandler;

use EonX\EasyActivity\Common\Store\StoreInterface;
use EonX\EasyActivity\Messenger\Message\ActivityLogEntryMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ActivityLogEntryMessageHandler
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
