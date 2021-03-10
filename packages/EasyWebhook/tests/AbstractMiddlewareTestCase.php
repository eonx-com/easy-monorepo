<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\Tests\Stubs\MiddlewareStub;

abstract class AbstractMiddlewareTestCase extends AbstractTestCase
{
    protected function process(
        MiddlewareInterface $middleware,
        WebhookInterface $webhook,
        ?WebhookResultInterface $webhookResult = null
    ): WebhookResultInterface {
        return $middleware->process($webhook, new Stack([new MiddlewareStub($webhookResult)]));
    }
}
