<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyLock\Common\ValueObject\LockDataInterface;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Middleware\LockMiddleware;
use EonX\EasyWebhook\Tests\Stub\Locker\LockerStub;
use PHPUnit\Framework\Attributes\DataProvider;

final class LockMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
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

    #[DataProvider('provideProcessData')]
    public function testProcess(WebhookInterface $webhook, bool $shouldLock, ?bool $canProcess = null): void
    {
        $canProcess ??= true;
        $expectedResource = \sprintf('easy_webhook_send_%s', $webhook->getId());
        $expectedResult = new WebhookResult($webhook);
        $lockerService = new LockerStub($canProcess);
        $middleware = new LockMiddleware($lockerService);

        $result = $this->process($middleware, $webhook, $expectedResult);
        $lockData = $lockerService->getLockData();

        switch ($shouldLock) {
            case true:
                self::assertInstanceOf(LockDataInterface::class, $lockData);
                self::assertSame($expectedResource, $lockData->getResource());

                break;
            case false:
                self::assertNull($lockerService->getLockData());
        }

        $canProcess
            ? self::assertSame($result, $expectedResult)
            : self::assertNotSame($result, $expectedResult);
    }
}
