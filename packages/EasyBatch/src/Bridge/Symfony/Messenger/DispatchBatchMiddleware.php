<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class DispatchBatchMiddleware implements MiddlewareInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchManagerInterface
     */
    private $batchManager;

    public function __construct(BatchManagerInterface $batchManager)
    {
        $this->batchManager = $batchManager;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $consumedByWorker = $envelope->last(ConsumedByWorkerStamp::class);
        $message = $envelope->getMessage();

        if ($consumedByWorker === null && $message instanceof BatchInterface) {
            $this->batchManager->dispatch($message);

            return $envelope;
        }

        return $stack->next()
            ->handle($envelope, $stack);
    }
}
