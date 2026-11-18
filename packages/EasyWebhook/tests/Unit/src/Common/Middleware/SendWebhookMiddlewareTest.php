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
                'no_proxy' => '',
                'on_progress' => null,
                'proxy' => null,
                'resolve' => [],
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'no_proxy as null' => [
            'options' => [
                'no_proxy' => null,
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'options that describe the request' => [
            'options' => [
                'auth_basic' => ['user', 'password'],
                'auth_bearer' => 'token',
                'body' => 'body',
                'buffer' => true,
                'capture_peer_cert_chain' => true,
                'headers' => [
                    'X-Some-Header' => 'value',
                ],
                'http_version' => '1.1',
                'json' => [
                    'key' => 'value',
                ],
                'max_connect_duration' => 1.0,
                'max_duration' => 5.0,
                'max_redirects' => 0,
                'query' => [
                    'key' => 'value',
                ],
                'timeout' => 5.0,
                'user_data' => 'user data',
                'verify_host' => true,
                'verify_peer' => true,
            ],
            'ssrfProtectionEnabled' => true,
        ];

        yield 'allowed extra keys' => [
            'options' => [
                'extra' => [
                    'no_cache' => true,
                    'trace_content' => false,
                    'use_persistent_connections' => true,
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
                'bindto' => '',
                'no_proxy' => '*',
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

        yield 'extra.curl as an empty array' => [
            'options' => [
                'extra' => [
                    'curl' => [],
                ],
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'extra.curl',
        ];

        yield 'extra key that is not allowed' => [
            'options' => [
                'extra' => [
                    'some_future_option' => true,
                ],
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'extra.some_future_option',
        ];

        yield 'extra as a string' => [
            'options' => [
                'extra' => 'curl',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'extra',
        ];

        yield 'verify_peer as false' => [
            'options' => [
                'verify_peer' => false,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'verify_peer',
        ];

        yield 'verify_peer as false with SSRF protection disabled' => [
            'options' => [
                'verify_peer' => false,
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'verify_peer',
        ];

        yield 'verify_host as false' => [
            'options' => [
                'verify_host' => false,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'verify_host',
        ];

        yield 'verify_host as false with SSRF protection disabled' => [
            'options' => [
                'verify_host' => false,
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'verify_host',
        ];

        yield 'verify_peer as null' => [
            'options' => [
                'verify_peer' => null,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'verify_peer',
        ];

        yield 'verify_host as an integer' => [
            'options' => [
                'verify_host' => 1,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'verify_host',
        ];

        yield 'cafile' => [
            'options' => [
                'cafile' => '/tmp/ca.pem',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'cafile',
        ];

        yield 'capath' => [
            'options' => [
                'capath' => '/tmp/ca',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'capath',
        ];

        yield 'local_cert' => [
            'options' => [
                'local_cert' => '/tmp/cert.pem',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'local_cert',
        ];

        yield 'local_pk' => [
            'options' => [
                'local_pk' => '/tmp/key.pem',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'local_pk',
        ];

        yield 'passphrase' => [
            'options' => [
                'passphrase' => 'passphrase',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'passphrase',
        ];

        yield 'ciphers' => [
            'options' => [
                'ciphers' => 'NULL',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'ciphers',
        ];

        yield 'crypto_method' => [
            'options' => [
                'crypto_method' => \STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'crypto_method',
        ];

        yield 'peer_fingerprint as sha256' => [
            'options' => [
                'peer_fingerprint' => [
                    'sha256' => 'fingerprint',
                ],
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'peer_fingerprint',
        ];

        yield 'peer_fingerprint as pin-sha256' => [
            'options' => [
                'peer_fingerprint' => [
                    'pin-sha256' => ['fingerprint'],
                ],
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'peer_fingerprint',
        ];

        yield 'base_uri' => [
            'options' => [
                'base_uri' => 'https://eonx.com',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'base_uri',
        ];

        yield 'cafile as null' => [
            'options' => [
                'cafile' => null,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'cafile',
        ];

        yield 'unknown option' => [
            'options' => [
                'some_future_option' => true,
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'some_future_option',
        ];

        yield 'unknown option with SSRF protection disabled' => [
            'options' => [
                'some_future_option' => true,
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'some_future_option',
        ];

        yield 'bindto' => [
            'options' => [
                'bindto' => '/var/run/some.sock',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'bindto',
        ];

        yield 'bindto as a socket path with SSRF protection disabled' => [
            'options' => [
                'bindto' => '/var/run/docker.sock',
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'bindto',
        ];

        yield 'bindto as a local address with SSRF protection disabled' => [
            'options' => [
                'bindto' => '127.0.0.1:0',
            ],
            'ssrfProtectionEnabled' => false,
            'expectedOption' => 'bindto',
        ];

        yield 'no_proxy for every host' => [
            'options' => [
                'no_proxy' => '*',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'no_proxy',
        ];

        yield 'no_proxy as a zero' => [
            'options' => [
                'no_proxy' => '0',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'no_proxy',
        ];

        yield 'no_proxy for a host' => [
            'options' => [
                'no_proxy' => 'eonx.com',
            ],
            'ssrfProtectionEnabled' => true,
            'expectedOption' => 'no_proxy',
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

    public function testProcessRejectsAllNotAllowedHttpClientOptionsInOneMessage(): void
    {
        $httpClient = new HttpClientStub();
        $middleware = new SendWebhookMiddleware($httpClient);
        $webhook = Webhook::fromArray([
            'url' => 'https://eonx.com',
        ])->httpClientOptions([
            'verify_peer' => false,
            'headers' => [
                'X-Some-Header' => 'value',
            ],
            'cafile' => '/tmp/ca.pem',
            'extra' => [
                'curl' => [
                    10082 => '/tmp/cookies.txt', // Value of the CURLOPT_COOKIEJAR constant
                ],
                'trace_content' => false,
                'some_future_option' => true,
            ],
        ]);

        $result = $this->process($middleware, $webhook);

        self::assertInstanceOf(WebhookHttpClientOptionNotAllowedException::class, $result->getThrowable());
        self::assertSame(
            'HTTP client options not allowed for a webhook: '
            . '"verify_peer", "cafile", "extra.curl", "extra.some_future_option".',
            $result->getThrowable()
                ->getMessage()
        );
        self::assertNull($httpClient->getOptions());
    }

    public function testProcessRejectsConnectionOptionByDefault(): void
    {
        $httpClient = new HttpClientStub();
        $middleware = new SendWebhookMiddleware($httpClient, null);
        $webhook = Webhook::fromArray([
            'url' => 'https://eonx.com',
        ])->httpClientOptions([
            'proxy' => 'http://proxy.eonx.com',
        ]);

        $result = $this->process($middleware, $webhook);

        self::assertInstanceOf(WebhookHttpClientOptionNotAllowedException::class, $result->getThrowable());
        self::assertNull($httpClient->getOptions());
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
            \sprintf('HTTP client options not allowed for a webhook: "%s".', $expectedOption),
            $result->getThrowable()
                ->getMessage()
        );
        self::assertNull($result->getResponse());
        self::assertNull($httpClient->getOptions());
    }
}
