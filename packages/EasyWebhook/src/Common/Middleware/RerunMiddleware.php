<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\CannotRerunWebhookException;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class RerunMiddleware extends AbstractMiddleware
{
    private const SHOULD_NOT_RERUN = [WebhookInterface::STATUS_FAILED, WebhookInterface::STATUS_SUCCESS];

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if (\in_array($webhook->getStatus(), self::SHOULD_NOT_RERUN, true)) {
            if ($webhook->isRerunAllowed() === false) {
                throw new CannotRerunWebhookException(\sprintf(
                    'Cannot re-run webhook "%s"',
                    $webhook->getId() ?? \spl_object_hash($webhook)
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
