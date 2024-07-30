<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Client;

use EmptyIterator;
use EonX\EasyWebhook\Common\Client\WebhookClient;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Enum\MiddlewarePriority;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Formatter\JsonWebhookBodyFormatter;
use EonX\EasyWebhook\Common\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Common\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Common\Middleware\EventHeaderMiddleware;
use EonX\EasyWebhook\Common\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Common\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Common\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Common\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Common\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Common\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;
use EonX\EasyWebhook\Common\Stack\Stack;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Common\Store\ArrayResultStore;
use EonX\EasyWebhook\Common\Store\ArrayStore;
use EonX\EasyWebhook\Common\Store\ResultStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;
use EonX\EasyWebhook\Tests\Stub\Dispatcher\AsyncDispatcherStub;
use EonX\EasyWebhook\Tests\Stub\HttpClient\HttpClientStub;
use EonX\EasyWebhook\Tests\Stub\Store\ArrayStoreStub;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebhookClientTest extends AbstractUnitTestCase
{
    /**
     * @see testSend
     */
    public static function provideSendData(): iterable
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
            [new BodyFormatterMiddleware(new JsonWebhookBodyFormatter())],
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
            new EmptyIterator(),
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
                new BodyFormatterMiddleware(new JsonWebhookBodyFormatter()),
                new SignatureHeaderMiddleware(new Rs256WebhookSigner(), 'my-secret'),
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
                    self::getRandomGenerator(),
                    '78981b69-535d-4483-8d94-2ef7cbdb07c8'
                )),
            ],
        ];
    }

    public function testDispatchAsyncKeepsStatusPending(): void
    {
        $webhook = Webhook::create('https://eonx.com');
        $webhookClient = new WebhookClient(new Stack([
            new StatusAndAttemptMiddleware(),
            new AsyncMiddleware(new AsyncDispatcherStub(), $this->getArrayStore()),
        ]));

        $webhookClient->sendWebhook($webhook);

        self::assertSame(WebhookStatus::Pending, $webhook->getStatus());
    }

    /**
     * @param iterable<\EonX\EasyWebhook\Common\Middleware\MiddlewareInterface>|null $middleware
     */
    #[DataProvider('provideSendData')]
    public function testSend(
        WebhookInterface $webhook,
        string $method,
        string $url,
        array $httpClientOptions,
        ?iterable $middleware = null,
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
        return new ArrayResultStore(self::getRandomGenerator(), $this->getDataCleaner());
    }

    private function getArrayStore(): ArrayStore
    {
        return new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
    }

    /**
     * @param iterable<\EonX\EasyWebhook\Common\Middleware\MiddlewareInterface>|null $middleware
     */
    private function getStack(
        HttpClientInterface $httpClient,
        StoreInterface $store,
        ResultStoreInterface $resultStore,
        ?iterable $middleware = null,
    ): StackInterface {
        $middlewareArray = [
            new StoreMiddleware($store, $resultStore, MiddlewarePriority::CoreAfter->value),
            new SendWebhookMiddleware($httpClient, MiddlewarePriority::CoreAfter->value + 1),
        ];

        if ($middleware !== null) {
            foreach ($middleware as $item) {
                $middlewareArray[] = $item;
            }
        }

        return new Stack($middlewareArray);
    }
}
