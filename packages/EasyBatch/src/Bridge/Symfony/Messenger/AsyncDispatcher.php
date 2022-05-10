<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;
use EonX\EasyBatch\Exceptions\BatchItemInvalidException;
use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncDispatcher implements AsyncDispatcherInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatchItem(BatchItemInterface $batchItem): void
    {
        $batchItemId = $batchItem->getIdOrFail();

        if ($batchItem->getType() === BatchItemInterface::TYPE_MESSAGE) {
            $message = $batchItem->getMessage();

            if ($message === null) {
                throw new BatchItemInvalidException(\sprintf(
                    'BatchItem "%s" is type of "%s" but has no message set',
                    $batchItemId,
                    $batchItem->getType()
                ));
            }

            $this->bus->dispatch($message, [new BatchItemStamp($batchItemId)]);

            return;
        }

        throw new BatchItemInvalidException(\sprintf(
            'BatchItem "%s" is not type of "%s", "%s" given',
            $batchItemId,
            BatchItemInterface::TYPE_MESSAGE,
            $batchItem->getType()
        ));
    }
}
