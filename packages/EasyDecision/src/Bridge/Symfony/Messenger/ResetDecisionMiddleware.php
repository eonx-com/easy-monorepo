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
    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionFactoryInterface
     */
    private $decisionFactory;

    public function __construct(DecisionFactoryInterface $decisionFactory)
    {
        $this->decisionFactory = $decisionFactory;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($this->shouldSkip($envelope)) {
            return $stack->next()
                ->handle($envelope, $stack);
        }

        $this->decisionFactory->reset();

        return $envelope;
    }

    private function shouldSkip(Envelope $envelope): bool
    {
        if ($envelope->last(ConsumedByWorkerStamp::class) === null) {
            return true;
        }

        return false;
    }
}
