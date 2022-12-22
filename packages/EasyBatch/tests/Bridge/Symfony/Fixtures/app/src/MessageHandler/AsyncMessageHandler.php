<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\MessageHandler;

use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\Message\AsyncMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AsyncMessageHandler
{
    private int $invokeCount = 0;

    public function __invoke(AsyncMessage $asyncMessage): void
    {
        $this->invokeCount++;
    }

    public function getInvokeCount(): int
    {
        return $this->invokeCount;
    }
}
