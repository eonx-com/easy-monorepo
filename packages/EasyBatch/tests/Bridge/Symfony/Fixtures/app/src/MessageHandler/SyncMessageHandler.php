<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\MessageHandler;

use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\Message\SyncMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SyncMessageHandler
{
    private int $invokeCount = 0;

    public function __invoke(SyncMessage $syncMessage): void
    {
        $this->invokeCount++;
    }

    public function getInvokeCount(): int
    {
        return $this->invokeCount;
    }
}
