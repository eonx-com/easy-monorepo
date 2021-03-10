<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Middleware\EventHeaderMiddleware;
use EonX\EasyWebhook\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Signers\Rs256Signer;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\Stores\ArrayResultStore;
use EonX\EasyWebhook\Stores\ArrayStore;
use EonX\EasyWebhook\Tests\Stubs\ArrayStoreStub;
use EonX\EasyWebhook\Tests\Stubs\HttpClientStub;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebhookClientTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testSend
     */
    public function providerTestSend(): iterable
    {
        yield 'Simple URL' => [
            (new Webhook())->url('https://eonx.com'),
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [],
        ];

        yield 'Method from Webhook has priority' => [
            (new Webhook())
                ->url('https://eonx.com')
                ->method('PUT'),
            'PUT',
            'https://eonx.com',
            [],
            [new MethodMiddleware('PATCH')],
        ];

        yield 'Body formatter with header' => [
            (new Webhook())
                ->url('https://eonx.com')
                ->body([
                    'key' => 'value',
                ]),
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => '{"key":"value"}',
            ],
            [new BodyFormatterMiddleware(new JsonFormatter())],
        ];

        yield 'Configurator priorities run higher last' => [
            (new Webhook())->url('https://eonx.com'),
            'PUT',
            'https://eonx.com',
            [],
            [new MethodMiddleware('PATCH', 200), new MethodMiddleware('PUT', 100)],
        ];

        yield 'Configurators as Traversable' => [
            (new Webhook())->url('https://eonx.com'),
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [],
            new \EmptyIterator(),
        ];

        yield 'RS256 Signature' => [
            (new Webhook())
                ->url('https://eonx.com')
                ->body([
                    'key' => 'value',
                ]),
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => 'fbde39337b529a887fba290e322809bd8530d9ba68d2c4c869d1394cc07bd99e',
                ],
                'body' => '{"key":"value"}',
            ],
            [
                new BodyFormatterMiddleware(new JsonFormatter()),
                new SignatureHeaderMiddleware(new Rs256Signer(), 'my-secret'),
            ],
        ];

        yield 'Event header' => [
            (new Webhook())
                ->url('https://eonx.com')
                ->event('my-event'),
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [
                'headers' => [
                    'X-Webhook-Event' => 'my-event',
                ],
            ],
            [new EventHeaderMiddleware()],
        ];

        yield 'Id header' => [
            (new Webhook())->url('https://eonx.com'),
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [
                'headers' => [
                    'X-Webhook-Id' => '78981b69-535d-4483-8d94-2ef7cbdb07c8',
                ],
            ],
            [
                new IdHeaderMiddleware(new ArrayStoreStub(
                    $this->getRandomGenerator(),
                    '78981b69-535d-4483-8d94-2ef7cbdb07c8'
                )),
            ],
        ];
    }

    /**
     * @param null|iterable<\EonX\EasyWebhook\Interfaces\MiddlewareInterface> $middleware
     * @param mixed[] $httpClientOptions
     *
     * @dataProvider providerTestSend
     */
    public function testSend(
        WebhookInterface $webhook,
        string $method,
        string $url,
        array $httpClientOptions,
        ?iterable $middleware = null
    ): void {
        $httpClient = new HttpClientStub();
        $store = $this->getArrayStore();
        $resultStore = $this->getArrayResultStore();
        $webhookClient = new WebhookClient($this->getStack($httpClient, $store, $resultStore, $middleware));

        $webhookClient->sendWebhook($webhook);

        self::assertInstanceOf(StackInterface::class, $webhookClient->getStack());
        self::assertEquals($method, $httpClient->getMethod());
        self::assertEquals($url, $httpClient->getUrl());
        self::assertEquals($httpClientOptions, $httpClient->getOptions());
    }

    private function getArrayResultStore(): ArrayResultStore
    {
        return new ArrayResultStore($this->getRandomGenerator());
    }

    private function getArrayStore(): ArrayStore
    {
        return new ArrayStore($this->getRandomGenerator());
    }

    /**
     * @param null|iterable<\EonX\EasyWebhook\Interfaces\MiddlewareInterface> $middleware
     */
    private function getStack(
        HttpClientInterface $httpClient,
        StoreInterface $store,
        ResultStoreInterface $resultStore,
        ?iterable $middleware = null
    ): StackInterface {
        $middlewareArray = [
            new StoreMiddleware($store, $resultStore, MiddlewareInterface::PRIORITY_CORE_AFTER),
            new SendWebhookMiddleware($httpClient, MiddlewareInterface::PRIORITY_CORE_AFTER + 1),
        ];

        if ($middleware !== null) {
            foreach ($middleware as $item) {
                $middlewareArray[] = $item;
            }
        }

        return new Stack($middlewareArray);
    }
}
