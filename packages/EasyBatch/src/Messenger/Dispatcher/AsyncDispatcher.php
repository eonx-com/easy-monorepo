<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Messenger\Dispatcher;

use EonX\EasyBatch\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyBatch\Common\Exception\BatchItemInvalidException;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyBatch\Messenger\Stamp\BatchItemStamp;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AsyncDispatcher implements AsyncDispatcherInterface
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
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
