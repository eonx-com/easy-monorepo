<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Doctrine\ManagersClearer;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class DoctrineManagersClearMiddleware implements MiddlewareInterface
{
    /**
     * @var null|string[]
     */
    private $managers;

    /**
     * @var \EonX\EasyAsync\Doctrine\ManagersClearer
     */
    private $managersClearer;

    /**
     * @param null|string[] $managers
     */
    public function __construct(ManagersClearer $managersClearer, ?array $managers = null)
    {
        $this->managersClearer = $managersClearer;
        $this->managers = $managers;
    }

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
