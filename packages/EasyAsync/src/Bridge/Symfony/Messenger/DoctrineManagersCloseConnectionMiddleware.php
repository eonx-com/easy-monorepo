<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Doctrine\ManagersCloser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class DoctrineManagersCloseConnectionMiddleware implements MiddlewareInterface
{
    /**
     * @param string[]|null $managers
     */
    public function __construct(
        private readonly ManagersCloser $managersCloser,
        private readonly ?array $managers = null,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            return $stack->next()
                ->handle($envelope, $stack);
        } finally {
            if ($envelope->last(ConsumedByWorkerStamp::class) !== null) {
                $this->managersCloser->close($this->managers);
            }
        }
    }
}
