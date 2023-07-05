<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;

final class BodyFormatterMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
    {
        yield 'body null' => [
            Webhook::fromArray([
                'body' => null,
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                self::assertNull($webhookResult->getWebhook()->getHttpClientOptions()['headers'] ?? null);
            },
        ];

        yield 'body empty' => [
            Webhook::fromArray([
                'body' => [],
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                self::assertNull($webhookResult->getWebhook()->getHttpClientOptions()['headers'] ?? null);
            },
        ];

        yield 'body empty string' => [
            Webhook::fromArray([
                'body' => [''],
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                $body = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['body'] ?? null;
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey('Content-Type', $headers);
                self::assertEquals('application/json', $headers['Content-Type']);
                self::assertJson($body);
                self::assertEquals('[""]', $body);
            },
        ];

        yield 'body associative array' => [
            Webhook::fromArray([
                'body' => [
                    'event' => 'my-event',
                    'key' => 'value',
                ],
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                $body = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['body'] ?? null;
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey('Content-Type', $headers);
                self::assertEquals('application/json', $headers['Content-Type']);
                self::assertJson($body);
                self::assertEquals('{"event":"my-event","key":"value"}', $body);
            },
        ];

        yield 'json associative array' => [
            Webhook::fromArray([])->mergeHttpClientOptions([
                'json' => [
                    'event' => 'my-event',
                    'key' => 'value',
                ],
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                $body = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['body'] ?? null;
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey('Content-Type', $headers);
                self::assertEquals('application/json', $headers['Content-Type']);
                self::assertJson($body);
                self::assertEquals('{"event":"my-event","key":"value"}', $body);
            },
        ];
    }

    /**
     * @dataProvider providerTestProcess
     */
    public function testProcess(
        WebhookInterface $webhook,
        callable $test,
        ?WebhookBodyFormatterInterface $bodyFormatter = null,
    ): void {
        $middleware = new BodyFormatterMiddleware($bodyFormatter ?? new JsonFormatter());

        $test($this->process($middleware, $webhook));
    }
}
