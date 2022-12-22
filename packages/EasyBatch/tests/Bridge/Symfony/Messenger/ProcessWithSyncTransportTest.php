<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Interfaces\BatchFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Objects\MessageDecorator;
use EonX\EasyBatch\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\Message\SyncMessage;
use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\MessageHandler\SyncMessageHandler;
use EonX\EasyTest\Traits\MessengerAssertionsTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class ProcessWithSyncTransportTest extends AbstractSymfonyTestCase
{
    use MessengerAssertionsTrait;

    public function testBatchProcessedSucceeds(): void
    {
        $message = new SyncMessage();
        $batch = self::getContainer()->get(BatchFactoryInterface::class)->createFromObject(
            MessageDecorator::wrap($message)
        );

        self::getContainer()->get(MessageBusInterface::class)->dispatch($batch);

        self::assertSame(1, self::getContainer()->get(SyncMessageHandler::class)->getInvokeCount());
        $batchRepository = self::getContainer()->get(BatchRepositoryInterface::class);
        $batchRepository->reset();
        $batch = $batchRepository->findOrFail((string)$batch->getId());
        self::assertSame('succeeded', $batch->getStatus());
    }
}
