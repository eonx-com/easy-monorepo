<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Middleware;

use EonX\EasyAsync\Doctrine\Clearer\ManagersClearer;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class DoctrineManagersClearMiddleware implements MiddlewareInterface
{
    /**
     * @param string[]|null $managers
     */
    public function __construct(
        private readonly ManagersClearer $managersClearer,
        private readonly ?array $managers = null,
    ) {
    }

    /**
     * @throws \EonX\EasyAsync\Doctrine\Exception\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exception\DoctrineManagerClosedException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $fromWorker = $envelope->last(ConsumedByWorkerStamp::class);

        if ($fromWorker instanceof ConsumedByWorkerStamp) {
            $this->managersClearer->clear($this->managers);
        }

        return $stack->next()
            ->handle($envelope, $stack);
    }
}
