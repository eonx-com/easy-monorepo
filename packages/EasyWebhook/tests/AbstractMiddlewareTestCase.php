<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\Tests\Stubs\MiddlewareStub;
use EonX\EasyWebhook\Tests\Stubs\StackThrowStub;
use Throwable;

abstract class AbstractMiddlewareTestCase extends AbstractTestCase
{
    protected function process(
        MiddlewareInterface $middleware,
        WebhookInterface $webhook,
        ?WebhookResultInterface $webhookResult = null,
        ?StackInterface $stack = null,
    ): WebhookResultInterface {
        return $middleware->process($webhook, $stack ?? new Stack([new MiddlewareStub($webhookResult)]));
    }

    protected function processWithThrowable(
        MiddlewareInterface $middleware,
        WebhookInterface $webhook,
        Throwable $throwable,
    ): WebhookResultInterface {
        return $middleware->process($webhook, new StackThrowStub($throwable));
    }
}
