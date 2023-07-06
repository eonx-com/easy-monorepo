<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyLock\Interfaces\LockDataInterface;
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
     *
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
    {
        yield 'should not lock (no id, not send now) -> return result from stack' => [Webhook::fromArray([]), false];

        yield 'should not lock (id, not send now) -> return result from stack' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_ID => 'my-id',
            ]),
            false,
        ];

        yield 'should lock and acquire lock -> return result from stack' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_ID => 'my-id',
                WebhookInterface::OPTION_SEND_NOW => true,
            ]),
            true,
        ];

        yield 'should lock but not acquire lock -> return new result' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_ID => 'my-id',
                WebhookInterface::OPTION_SEND_NOW => true,
            ]),
            true,
            false,
        ];
    }

    /**
     * @dataProvider providerTestProcess
     */
    public function testProcess(WebhookInterface $webhook, bool $shouldLock, ?bool $canProcess = null): void
    {
        $canProcess = $canProcess ?? true;
        $expectedResource = \sprintf('easy_webhook_send_%s', $webhook->getId());
        $expectedResult = new WebhookResult($webhook);
        $lockService = new LockServiceStub($canProcess);
        $middleware = new LockMiddleware($lockService);

        $result = $this->process($middleware, $webhook, $expectedResult);
        $lockData = $lockService->getLockData();

        switch ($shouldLock) {
            case true:
                self::assertInstanceOf(LockDataInterface::class, $lockData);
                /** @var \EonX\EasyLock\Interfaces\LockDataInterface $lockData */
                self::assertEquals($expectedResource, $lockData->getResource());
                break;
            case false:
                self::assertNull($lockService->getLockData());
        }

        $canProcess
            ? self::assertSame($result, $expectedResult)
            : self::assertNotSame($result, $expectedResult);
    }
}
