<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyWebhook\Configurators\BodyFormatterWebhookConfigurator;
use EonX\EasyWebhook\Configurators\MethodWebhookConfigurator;
use EonX\EasyWebhook\Configurators\SignatureWebhookConfigurator;
use EonX\EasyWebhook\Exceptions\InvalidWebhookUrlException;
use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\RetryStrategies\ExponentialWebhookRetryStrategy;
use EonX\EasyWebhook\Signers\Rs256Signer;
use EonX\EasyWebhook\Stores\ArrayWebhookResultStore;
use EonX\EasyWebhook\Tests\Stubs\HttpClientStub;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookClient;
use EonX\EasyWebhook\WebhookResultHandler;

final class WebhookClientTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSend(): iterable
    {
        yield 'Simple URL' => [
            (new Webhook())->setUrl('https://eonx.com'),
            null,
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [],
        ];

        yield 'Method from Webhook has priority' => [
            (new Webhook())
                ->setUrl('https://eonx.com')
                ->setMethod('PUT'),
            [new MethodWebhookConfigurator('PATCH')],
            'PUT',
            'https://eonx.com',
            [],
        ];

        yield 'Body formatter with header' => [
            (new Webhook())
                ->setUrl('https://eonx.com')
                ->setBody(['key' => 'value']),
            [new BodyFormatterWebhookConfigurator(new JsonFormatter())],
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => '{"key":"value"}',
            ],
        ];

        yield 'Configurator priorities run higher last' => [
            (new Webhook())->setUrl('https://eonx.com'),
            [
                new MethodWebhookConfigurator('PATCH', 200),
                new MethodWebhookConfigurator('PUT', 100),
            ],
            'PATCH',
            'https://eonx.com',
            [],
        ];

        yield 'Configurators as Traversable' => [
            (new Webhook())->setUrl('https://eonx.com'),
            new \EmptyIterator(),
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [],
        ];

        yield 'RS256 Signature' => [
            (new Webhook())
                ->setUrl('https://eonx.com')
                ->setBody(['key' => 'value']),
            [
                new BodyFormatterWebhookConfigurator(new JsonFormatter()),
                new SignatureWebhookConfigurator(new Rs256Signer(), 'my-secret'),
            ],
            WebhookInterface::DEFAULT_METHOD,
            'https://eonx.com',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Signature' => 'fbde39337b529a887fba290e322809bd8530d9ba68d2c4c869d1394cc07bd99e',
                ],
                'body' => '{"key":"value"}',
            ],
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
        ?iterable $configurators,
        string $method,
        string $url,
        array $httpClientOptions
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
