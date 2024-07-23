<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Formatter\JsonWebhookBodyFormatter;
use EonX\EasyWebhook\Common\Formatter\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Common\Middleware\BodyFormatterMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;

final class BodyFormatterMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
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

    #[DataProvider('provideProcessData')]
    public function testProcess(
        WebhookInterface $webhook,
        callable $test,
        ?WebhookBodyFormatterInterface $bodyFormatter = null,
    ): void {
        $middleware = new BodyFormatterMiddleware($bodyFormatter ?? new JsonWebhookBodyFormatter());

        $test($this->process($middleware, $webhook));
    }
}
