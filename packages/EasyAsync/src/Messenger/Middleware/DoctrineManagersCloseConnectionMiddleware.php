<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Middleware;

use EonX\EasyAsync\Doctrine\Closer\ManagersCloser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final readonly class DoctrineManagersCloseConnectionMiddleware implements MiddlewareInterface
{
    /**
     * @param string[]|null $managers
     */
    public function __construct(
        private ManagersCloser $managersCloser,
        private ?array $managers = null,
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
