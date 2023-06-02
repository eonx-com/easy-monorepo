<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Exceptions\CannotRerunWebhookException;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class RerunMiddleware extends AbstractMiddleware
{
    /**
     * @var string[]
     */
    private const SHOULD_NOT_RERUN = [WebhookInterface::STATUS_FAILED, WebhookInterface::STATUS_SUCCESS];

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if (\in_array($webhook->getStatus(), self::SHOULD_NOT_RERUN, true)) {
            if ($webhook->isRerunAllowed() === false) {
                throw new CannotRerunWebhookException(\sprintf(
                    'Cannot re-run webhook "%s"',
                    $webhook->getId() ?? \spl_object_hash($webhook),
                ));
            }

            // Reset webhook status and currentAttempt of rerun
            $webhook
                ->status(WebhookInterface::STATUS_PENDING)
                ->currentAttempt(WebhookInterface::DEFAULT_CURRENT_ATTEMPT);
        }

        return $this->passOn($webhook, $stack);
    }
}
