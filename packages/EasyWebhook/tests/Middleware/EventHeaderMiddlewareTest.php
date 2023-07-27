<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\EventHeaderMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;

final class EventHeaderMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
    {
        yield 'no event' => [
            Webhook::fromArray([]),
            static function (WebhookResultInterface $webhookResult): void {
                self::assertNull($webhookResult->getWebhook()->getHttpClientOptions()['headers'] ?? null);
            },
        ];

        yield 'event with default header' => [
            Webhook::fromArray([
                'event' => 'my-event',
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey(WebhookInterface::HEADER_EVENT, $headers);
                self::assertEquals('my-event', $headers[WebhookInterface::HEADER_EVENT]);
            },
        ];

        yield 'event with custom header' => [
            Webhook::fromArray([
                'event' => 'my-event',
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey('X-My-Event', $headers);
                self::assertEquals('my-event', $headers['X-My-Event']);
            },
            'X-My-Event',
        ];
    }

    /**
     * @dataProvider providerTestProcess
     */
    public function testProcess(WebhookInterface $webhook, callable $test, ?string $eventHeader = null): void
    {
        $middleware = new EventHeaderMiddleware($eventHeader);

        $test($this->process($middleware, $webhook));
    }
}
