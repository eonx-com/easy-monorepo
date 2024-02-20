<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Messenger;

use EonX\EasyActivity\Interfaces\StoreInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ActivityLogEntryMessageHandler
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
