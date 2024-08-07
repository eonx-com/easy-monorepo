<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Middleware\MiddlewareInterface;
use EonX\EasyWebhook\Common\Stack\Stack;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Tests\Stub\Middleware\MiddlewareStub;
use EonX\EasyWebhook\Tests\Stub\Stack\WithThrowableStackStub;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use Throwable;

abstract class AbstractMiddlewareTestCase extends AbstractUnitTestCase
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
        return $middleware->process($webhook, new WithThrowableStackStub($throwable));
    }
}
