<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Stack\StackInterface;

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
                $webhook->status(WebhookStatus::Success);

                break;
            case false:
                $webhook->status(
                    $webhook->getCurrentAttempt() >= $webhook->getMaxAttempt()
                        ? WebhookStatus::Failed
                        : WebhookStatus::FailedPendingRetry
                );
        }

        return $webhookResult;
    }
}
