<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;

final class MethodMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
    {
        yield 'default method' => [Webhook::fromArray([]), WebhookInterface::DEFAULT_METHOD];

        yield 'custom default method' => [Webhook::fromArray([]), 'PUT', 'PUT'];

        yield 'custom method on webhook' => [
            Webhook::fromArray([
                'method' => 'PATCH',
            ]),
            'PATCH',
        ];
    }

    /**
     * @dataProvider providerTestProcess
     */
    public function testProcess(WebhookInterface $webhook, string $expectedMethod, ?string $defaultMethod = null): void
    {
        $middleware = new MethodMiddleware($defaultMethod);
        $result = $this->process($middleware, $webhook);

        self::assertEquals($expectedMethod, $result->getWebhook()->getMethod());
    }

    public function testProcessWithConfiguredWebhook(): void
    {
        $webhook = (new Webhook())->configured(true);
        $middleware = new MethodMiddleware(null, 10);

        $this->process($middleware, $webhook);

        self::assertNull($webhook->getMethod());
    }
}
