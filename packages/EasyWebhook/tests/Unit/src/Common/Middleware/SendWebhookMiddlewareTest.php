<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\InvalidWebhookUrlException;
use EonX\EasyWebhook\Common\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Tests\Stub\HttpClient\HttpClientStub;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

final class SendWebhookMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
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
     */
    #[DataProvider('provideProcessData')]
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
