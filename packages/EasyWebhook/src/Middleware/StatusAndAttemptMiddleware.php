<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class StatusAndAttemptMiddleware extends AbstractMiddleware
{
    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhookResult = $this->passOn($webhook, $stack);

        // Early return if result wasn't even attempted
        if ($webhookResult->isAttempted() === false) {
            return $webhookResult;
        }

        $webhook = $webhookResult->getWebhook();

        // Update current attempt only if needed
        if ($webhook->getCurrentAttempt() < $webhook->getMaxAttempt()) {
            $webhook->currentAttempt($webhook->getCurrentAttempt() + 1);
        }

        switch ($webhookResult->isSuccessful()) {
            case true:
                $webhook->status(WebhookInterface::STATUS_SUCCESS);
                break;
            case false:
                $webhook->status(
                    $webhook->getCurrentAttempt() >= $webhook->getMaxAttempt()
                        ? WebhookInterface::STATUS_FAILED
                        : WebhookInterface::STATUS_FAILED_PENDING_RETRY,
                );
        }

        return $webhookResult;
    }
}
