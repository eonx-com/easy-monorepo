<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcher;
use EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Event\FailedWebhookEvent;
use EonX\EasyWebhook\Common\Event\FinalFailedWebhookEvent;
use EonX\EasyWebhook\Common\Event\SuccessWebhookEvent;
use EonX\EasyWebhook\Common\Middleware\EventsMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

final class EventsMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'No event for pending' => [];

        yield 'Success' => [
            Webhook::fromArray([
                'status' => WebhookStatus::Success->value,
            ]),
            SuccessWebhookEvent::class,
        ];

        yield 'Failed pending retry' => [
            Webhook::fromArray([
                'status' => WebhookStatus::FailedPendingRetry->value,
            ]),
            FailedWebhookEvent::class,
        ];

        yield 'Final failed' => [
            Webhook::fromArray([
                'status' => WebhookStatus::Failed->value,
            ]),
            FinalFailedWebhookEvent::class,
        ];
    }

    #[DataProvider('provideProcessData')]
    public function testProcess(?WebhookInterface $webhook = null, ?string $eventClass = null): void
    {
        $dispatcher = new EventDispatcherStub(
            new EventDispatcher(
                new SymfonyEventDispatcher()
            )
        );
        $middleware = new EventsMiddleware($dispatcher);

        $this->process($middleware, new Webhook(), new WebhookResult($webhook ?? new Webhook()));
        $dispatched = $dispatcher->getDispatchedEvents();

        if ($eventClass !== null) {
            self::assertCount(1, $dispatched);
            self::assertEquals($eventClass, $dispatched[0]::class);

            return;
        }

        self::assertCount(0, $dispatched);
    }
}
