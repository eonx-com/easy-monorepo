<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Messenger\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Strategy\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\Messenger\Message\SendWebhookMessage;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class RetrySendWebhookMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ContainerInterface $container,
        private WebhookRetryStrategyInterface $retryStrategy,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack
            ->next()
            ->handle($envelope, $stack);

        // Skip if not webhook message
        if (($envelope->getMessage() instanceof SendWebhookMessage) === false) {
            return $envelope;
        }

        $stamp = $envelope->last(ReceivedStamp::class);
        $result = $envelope
            ->getMessage()
            ->getResult();

        // Skip if message not received or not result set
        if (($stamp instanceof ReceivedStamp) === false || ($result instanceof WebhookResultInterface) === false) {
            return $envelope;
        }

        // Retry if result not successful and webhook is retryable
        if ($result->isSuccessful() === false && $this->retryStrategy->isRetryable($result->getWebhook())) {
            $delay = $this->retryStrategy->getWaitingTime($result->getWebhook());

            $retryEnvelope = $envelope
                ->withoutAll(HandledStamp::class)
                ->with(new DelayStamp($delay), new RedeliveryStamp($result->getWebhook()->getCurrentAttempt()));

            /** @var \EonX\EasyWebhook\Messenger\Message\SendWebhookMessage $message */
            $message = $retryEnvelope->getMessage();

            // Set result to null before sending back the message
            $message->setResult(null);

            $this->getTransport($stamp->getTransportName())
                ->send($retryEnvelope);
        }

        return $envelope;
    }

    private function getTransport(string $transportName): TransportInterface
    {
        /** @var \Symfony\Component\Messenger\Transport\TransportInterface $transport */
        $transport = $this->container->get($transportName);

        return $transport;
    }
}
