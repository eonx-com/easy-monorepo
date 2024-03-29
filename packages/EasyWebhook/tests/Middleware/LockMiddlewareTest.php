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
use PHPUnit\Framework\Attributes\DataProvider;

final class LockMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
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

    #[DataProvider('providerTestProcess')]
    public function testProcess(WebhookInterface $webhook, bool $shouldLock, ?bool $canProcess = null): void
    {
        $canProcess ??= true;
        $expectedResource = \sprintf('easy_webhook_send_%s', $webhook->getId());
        $expectedResult = new WebhookResult($webhook);
        $lockService = new LockServiceStub($canProcess);
        $middleware = new LockMiddleware($lockService);

        $result = $this->process($middleware, $webhook, $expectedResult);
        /** @var \EonX\EasyLock\Interfaces\LockDataInterface $lockData */
        $lockData = $lockService->getLockData();

        switch ($shouldLock) {
            case true:
                self::assertInstanceOf(LockDataInterface::class, $lockData);
                self::assertSame($expectedResource, $lockData->getResource());

                break;
            case false:
                self::assertNull($lockService->getLockData());
        }

        $canProcess
            ? self::assertSame($result, $expectedResult)
            : self::assertNotSame($result, $expectedResult);
    }
}
