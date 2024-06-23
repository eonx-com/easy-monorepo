<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\DoNotHandleMeEasyWebhookExceptionInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use Throwable;

final class HandleExceptionsMiddleware extends AbstractMiddleware
{
    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        try {
            return $this->passOn($webhook, $stack);
        } catch (Throwable $throwable) {
            if ($throwable instanceof DoNotHandleMeEasyWebhookExceptionInterface) {
                throw $throwable;
            }

            return new WebhookResult($webhook, null, $throwable);
        }
    }
}
