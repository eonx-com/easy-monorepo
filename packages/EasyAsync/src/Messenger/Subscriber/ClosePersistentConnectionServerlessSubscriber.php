<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Subscriber;

use EonX\EasyAsync\Doctrine\Closer\ConnectionCloser;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class ClosePersistentConnectionServerlessSubscriber implements EventSubscriberInterface
{
    private ?float $lastHandledAt = null;

    /**
     * @param string[]|null $managers
     */
    public function __construct(
        private readonly ConnectionCloser $connectionCloser,
        private readonly float $maxIdleTime,
        private readonly ?array $managers = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EnvelopeDispatchedEvent::class => 'onEnvelopeDispatched',
            WorkerMessageReceivedEvent::class => ['onWorkerMessageReceived', 10_000],
        ];
    }

    public function onEnvelopeDispatched(EnvelopeDispatchedEvent $event): void
    {
        $this->lastHandledAt = \microtime(true);
    }

    public function onWorkerMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        if ($this->lastHandledAt === null) {
            return;
        }

        if ((\microtime(true) - $this->lastHandledAt) < $this->maxIdleTime) {
            return;
        }

        $this->connectionCloser->close($this->managers);
        $this->lastHandledAt = null;
    }
}
