<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Events\FailedWebhookEvent;
use EonX\EasyWebhook\Events\FinalFailedWebhookEvent;
use EonX\EasyWebhook\Events\SuccessWebhookEvent;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Middleware\EventsMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\EventDispatcherStub;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

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

    /**
     * @dataProvider providerTestProcess
     */
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
