<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\InvalidWebhookUrlException;
use EonX\EasyWebhook\Common\Exception\WebhookHttpClientOptionNotAllowedException;
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
     * @see testProcessPassesAllowedHttpClientOptions
     */
    public static function provideAllowedHttpClientOptions(): iterable
    {
        yield 'empty values' => [
            'options' => [
                'bindto' => '0',
                'extra' => [
                    'curl' => [],
                ],
                'on_progress' => null,
                'proxy' => null,
                'resolve' => [],
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'other extra keys' => [
            'options' => [
                'extra' => [
                    'trace_content' => false,
                ],
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'on_progress as a closure' => [
            'options' => [
                'on_progress' => static function (): void {},
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'on_progress as an object and method pair' => [
            'options' => [
                'on_progress' => [
                    new class {
                        public function onProgress(): void {}
                    },
                    'onProgress',
                ],
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'on_progress as an invokable object' => [
            'options' => [
                'on_progress' => new class {
                    public function __invoke(): void {}
                },
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'connection options with SSRF protection disabled' => [
            'options' => [
                'bindto' => '127.0.0.1',
                'proxy' => 'http://proxy.eonx.com',
                'resolve' => [
                    'eonx.com' => '127.0.0.1',
                ],
            ],
            'ssrfProtectionEnabled' => false,
        ];
    }

    /**
     * @see testProcessRejectsNotAllowedHttpClientOption
     */
    public static function provideNotAllowedHttpClientOptions(): iterable
    {
        yield 'extra.curl' => [
            'options' => [
                'extra' => [
                    'curl' => [
                        10082 => '/tmp/cookies.txt', // Value of the CURLOPT_COOKIEJAR constant
                    ],
                ],
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'extra.curl',
        ];

        yield 'extra.curl with SSRF protection disabled' => [
            'options' => [
                'extra' => [
                    'curl' => [
                        10082 => '/tmp/cookies.txt', // Value of the CURLOPT_COOKIEJAR constant
                    ],
                ],
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'extra.curl',
        ];

        yield 'on_progress as a function name' => [
            'options' => [
                'on_progress' => 'some_function',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'on_progress',
        ];

        yield 'on_progress as a static method name' => [
            'options' => [
                'on_progress' => 'App\\SomeClass::someMethod',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'on_progress',
        ];

        yield 'on_progress as a class and method pair' => [
            'options' => [
                'on_progress' => ['App\\SomeClass', 'someMethod'],
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'on_progress',
        ];

        yield 'on_progress as a class and method pair with SSRF protection disabled' => [
            'options' => [
                'on_progress' => ['App\\SomeClass', 'someMethod'],
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'on_progress',
        ];

        yield 'on_progress as an empty array (a closure after it is stored)' => [
            'options' => [
                'on_progress' => [],
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'on_progress',
        ];

        yield 'on_progress as false' => [
            'options' => [
                'on_progress' => false,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'on_progress',
        ];

        yield 'on_progress as an integer' => [
            'options' => [
                'on_progress' => 0,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'on_progress',
        ];

        yield 'bindto' => [
            'options' => [
                'bindto' => '/var/run/some.sock',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'bindto',
        ];

        yield 'proxy' => [
            'options' => [
                'proxy' => 'http://127.0.0.1:8080',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'proxy',
        ];

        yield 'proxy as a string that is empty for PHP' => [
            'options' => [
                'proxy' => '0',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'proxy',
        ];

        yield 'proxy as an empty string' => [
            'options' => [
                'proxy' => '',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'proxy',
        ];

        yield 'proxy as a zero' => [
            'options' => [
                'proxy' => 0,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'proxy',
        ];

        yield 'resolve' => [
            'options' => [
                'resolve' => [
                    'eonx.com' => '127.0.0.1',
                ],
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'resolve',
        ];
    }

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

    #[DataProvider('provideAllowedHttpClientOptions')]
    public function testProcessPassesAllowedHttpClientOptions(array $options, bool $ssrfProtectionEnabled): void
    {
        $httpClient = new HttpClientStub();
        $middleware = new SendWebhookMiddleware($httpClient, null, $ssrfProtectionEnabled);
        $webhook = Webhook::fromArray([
            'url' => 'https://eonx.com',
        ])->httpClientOptions($options);

        $result = $this->process($middleware, $webhook);

        self::assertNull($result->getThrowable());
        self::assertSame($options, $httpClient->getOptions());
    }

    #[DataProvider('provideNotAllowedHttpClientOptions')]
    public function testProcessRejectsNotAllowedHttpClientOption(
        array $options,
        bool $ssrfProtectionEnabled,
        string $expectedOption,
    ): void {
        $httpClient = new HttpClientStub();
        $middleware = new SendWebhookMiddleware($httpClient, null, $ssrfProtectionEnabled);
        $webhook = Webhook::fromArray([
            'url' => 'https://eonx.com',
        ])->httpClientOptions($options);

        $result = $this->process($middleware, $webhook);

        self::assertInstanceOf(WebhookHttpClientOptionNotAllowedException::class, $result->getThrowable());
        self::assertSame(
            \sprintf('The "%s" HTTP client option is not allowed for a webhook.', $expectedOption),
            $result->getThrowable()
                ->getMessage()
        );
        self::assertNull($result->getResponse());
        self::assertNull($httpClient->getOptions());
    }
}
