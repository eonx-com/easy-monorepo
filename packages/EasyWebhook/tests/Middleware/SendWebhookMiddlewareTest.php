<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Exceptions\InvalidWebhookUrlException;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\HttpClientStub;
use EonX\EasyWebhook\Webhook;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

final class SendWebhookMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
    {
        yield 'empty url exception' => [Webhook::fromArray([]), null, null, InvalidWebhookUrlException::class];

        yield 'Success' => [
            Webhook::fromArray([
                'url' => 'https://eonx.com',
            ]),
            static function (WebhookResultInterface $webhookResult, HttpClientStub $httpClient): void {
                self::assertNull($webhookResult->getThrowable());
                self::assertInstanceOf(ResponseInterface::class, $webhookResult->getResponse());
                self::assertEquals('https://eonx.com', $httpClient->getUrl());
                self::assertEquals(WebhookInterface::DEFAULT_METHOD, $httpClient->getMethod());
                self::assertEmpty($httpClient->getOptions());
            },
        ];

        yield 'HTTP exception' => [
            Webhook::fromArray([
                'url' => 'https://eonx.com',
            ]),
            static function (WebhookResultInterface $webhookResult, HttpClientStub $httpClient): void {
                self::assertInstanceOf(Throwable::class, $webhookResult->getThrowable());
                self::assertInstanceOf(ResponseInterface::class, $webhookResult->getResponse());
                self::assertEquals('https://eonx.com', $httpClient->getUrl());
                self::assertEquals(WebhookInterface::DEFAULT_METHOD, $httpClient->getMethod());
                self::assertEmpty($httpClient->getOptions());
            },
            new ClientException(new MockResponse()),
        ];
    }

    /**
     * @phpstan-param class-string<\Throwable>|null $expectedException
     *
     * @dataProvider providerTestProcess
     */
    public function testProcess(
        WebhookInterface $webhook,
        ?callable $test = null,
        ?Throwable $throwable = null,
        ?string $expectedException = null,
    ): void {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $httpClient = new HttpClientStub($throwable);
        $middleware = new SendWebhookMiddleware($httpClient);

        $result = $this->process($middleware, $webhook);

        if ($test !== null) {
            $test($result, $httpClient);
        }
    }
}
