<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Event\FailedWebhookEvent;
use EonX\EasyWebhook\Common\Event\FinalFailedWebhookEvent;
use EonX\EasyWebhook\Common\Event\SuccessWebhookEvent;
use EonX\EasyWebhook\Common\Middleware\EventsMiddleware;
use EonX\EasyWebhook\Tests\Stub\Dispatcher\EventDispatcherStub;
use PHPUnit\Framework\Attributes\DataProvider;

final class EventsMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
    {
        yield 'No event for pending' => [];

        yield 'Success' => [
            Webhook::fromArray([
                'status' => WebhookInterface::STATUS_SUCCESS,
            ]),
            SuccessWebhookEvent::class,
        ];

        yield 'Failed pending retry' => [
            Webhook::fromArray([
                'status' => WebhookInterface::STATUS_FAILED_PENDING_RETRY,
            ]),
            FailedWebhookEvent::class,
        ];

        yield 'Final failed' => [
            Webhook::fromArray([
                'status' => WebhookInterface::STATUS_FAILED,
            ]),
            FinalFailedWebhookEvent::class,
        ];
    }

    #[DataProvider('providerTestProcess')]
    public function testProcess(?WebhookInterface $webhook = null, ?string $eventClass = null): void
    {
        $dispatcher = new EventDispatcherStub();
        $middleware = new EventsMiddleware($dispatcher);

        $this->process($middleware, new Webhook(), new WebhookResult($webhook ?? new Webhook()));
        $dispatched = $dispatcher->getDispatched();

        if ($eventClass !== null) {
            self::assertCount(1, $dispatched);
            self::assertEquals($eventClass, $dispatched[0]::class);

            return;
        }

        self::assertCount(0, $dispatched);
    }
}
