<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony\Messenger;

use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ResetDecisionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private DecisionFactoryInterface $decisionFactory,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(ConsumedByWorkerStamp::class) !== null) {
            $this->decisionFactory->reset();
        }

        return $stack->next()
            ->handle($envelope, $stack);
    }
}
