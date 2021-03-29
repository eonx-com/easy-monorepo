<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class SyncRetryMiddleware extends AbstractMiddleware
{
    /**
     * @var bool
     */
    private $asyncEnabled;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface
     */
    private $retryStrategy;

    public function __construct(
        WebhookRetryStrategyInterface $retryStrategy,
        ?bool $asyncEnabled = null,
        ?LoggerInterface $logger = null,
        ?int $priority = null
    ) {
        $this->retryStrategy = $retryStrategy;
        $this->asyncEnabled = $asyncEnabled ?? true;
        $this->logger = $logger ?? new NullLogger();

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if ($this->asyncEnabled || $webhook->getMaxAttempt() <= 1) {
            return $this->passOn($webhook, $stack);
        }

        $this->logger->debug(
            'Using the synchronous retry is a nice and simple solution.
            However, we strongly recommend to setup async feature and use a proper retry strategy within the queue.'
        );

        $rewindTo = $stack->getCurrentIndex();
        $safety = 0;

        do {
            $stack->rewindTo($rewindTo);

            if ($webhook->getCurrentAttempt() > 0) {
                \usleep($this->retryStrategy->getWaitingTime($webhook) * 1000);
            }

            $result = $this->passOn($webhook, $stack);
            $safety++;

            $shouldLoop = $result->isSuccessful() === false
                && $this->retryStrategy->isRetryable($webhook)
                && $safety < $webhook->getMaxAttempt();
        } while ($shouldLoop);

        return $result;
    }
}
