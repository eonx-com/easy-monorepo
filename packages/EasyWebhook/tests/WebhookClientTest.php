<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyWebhook\Configurators\BodyFormatterWebhookConfigurator;
use EonX\EasyWebhook\Configurators\EventWebhookConfigurator;
use EonX\EasyWebhook\Configurators\IdWebhookConfigurator;
use EonX\EasyWebhook\Configurators\MethodWebhookConfigurator;
use EonX\EasyWebhook\Configurators\SignatureWebhookConfigurator;
use EonX\EasyWebhook\Exceptions\InvalidWebhookUrlException;
use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Signers\Rs256Signer;
use EonX\EasyWebhook\Stores\ArrayWebhookResultStore;
use EonX\EasyWebhook\Tests\Stubs\ArrayWebhookResultStoreStub;
use EonX\EasyWebhook\Tests\Stubs\HttpClientStub;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookClient;
use EonX\EasyWebhook\WebhookResultHandler;

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
            [new MethodWebhookConfigurator('PATCH')],
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
            [new BodyFormatterWebhookConfigurator(new JsonFormatter())],
        ];

        yield 'Configurator priorities run higher last' => [
            (new Webhook())->url('https://eonx.com'),
            'PUT',
            'https://eonx.com',
            [],
            [new MethodWebhookConfigurator('PATCH', 200), new MethodWebhookConfigurator('PUT', 100)],
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
                new BodyFormatterWebhookConfigurator(new JsonFormatter()),
                new SignatureWebhookConfigurator(new Rs256Signer(), 'my-secret'),
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
            [new EventWebhookConfigurator()],
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
            [new IdWebhookConfigurator(new ArrayWebhookResultStoreStub('78981b69-535d-4483-8d94-2ef7cbdb07c8'))],
        ];
    }

    public function testInvalidUrlExceptionThrownWhenEmptyUrl(): void
    {
        $this->expectException(InvalidWebhookUrlException::class);

        $client = (new WebhookClient(new HttpClientStub(), new WebhookResultHandler($this->getArrayStore())));
        $client->sendWebhook(new Webhook());
    }

    /**
     * @param \EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface[] $configurators
     * @param mixed[] $httpClientOptions
     *
     * @dataProvider providerTestSend
     */
    public function testSend(
        WebhookInterface $webhook,
        string $method,
        string $url,
        array $httpClientOptions,
        ?iterable $configurators = null
    ): void {
        $httpClient = new HttpClientStub();
        $store = $this->getArrayStore();
        $webhookClient = new WebhookClient($httpClient, new WebhookResultHandler($store), $configurators);

        $webhookClient->sendWebhook($webhook);

        self::assertEquals($method, $httpClient->getMethod());
        self::assertEquals($url, $httpClient->getUrl());
        self::assertEquals($httpClientOptions, $httpClient->getOptions());
    }

    private function getArrayStore(): ArrayWebhookResultStore
    {
        return new ArrayWebhookResultStore((new RandomGenerator())->setUuidV4Generator(new RamseyUuidV4Generator()));
    }
}
