<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\DoNotHandleMeEasyWebhookExceptionInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\WebhookResult;
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
