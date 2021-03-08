<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Middleware\LockMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\LockServiceStub;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class LockMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestProcess(): iterable
    {
        yield 'acquire lock -> return result from stack' => [Webhook::fromArray([]), true];

        yield 'do not acquire lock -> return new result' => [
            Webhook::fromArray([
                'id' => 'my-id',
            ]),
            false,
        ];
    }

    /**
     * @dataProvider providerTestProcess
     */
    public function testProcess(WebhookInterface $webhook, bool $canProcess): void
    {
        $expectedResource = \sprintf('easy_webhook_send_%s', $webhook->getId() ?? \spl_object_hash($webhook));
        $expectedResult = new WebhookResult($webhook);
        $lockService = new LockServiceStub($canProcess);
        $middleware = new LockMiddleware($lockService);

        $result = $this->process($middleware, $webhook, $expectedResult);

        if ($lockService->getLockData() !== null) {
            self::assertEquals($expectedResource, $lockService->getLockData()->getResource());
        }

        $canProcess
            ? self::assertSame($result, $expectedResult)
            : self::assertNotSame($result, $expectedResult);
    }
}
