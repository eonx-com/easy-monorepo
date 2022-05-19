<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use Carbon\Carbon;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Stores\DoctrineDbalResultStore;
use EonX\EasyWebhook\Tests\AbstractStoreTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;
use Illuminate\Support\Arr;

final class DoctrineDbalResultStoreTest extends AbstractStoreTestCase
{
    public function testStore(): void
    {
        $store = new DoctrineDbalResultStore(
            $this->getRandomGenerator(),
            $this->getDoctrineDbalConnection(),
            $this->getDataCleaner()
        );
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)
            ->id('webhook-id');
        $result = new WebhookResult($webhook);

        $store->store($result);

        self::assertNotEmpty($result->getId());
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testStoreWithTimezone(): void
    {
        // Time in UTC, this is as 00:00:00.
        Carbon::setTestNow('2022-05-19');

        $webHookId = 'webhook-id';

        $conn = $this->getDoctrineDbalConnection();

        $store = new DoctrineDbalResultStore(
            $this->getRandomGenerator(),
            $conn,
            $this->getDataCleaner(),
            'easy_webhook_results',
            'Australia/Melbourne'
        );
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)
            ->id($webHookId);
        $result = new WebhookResult($webhook);

        $store->store($result);

        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', 'easy_webhook_results');

        /** @var mixed[] $data */
        $data = $conn->fetchAssociative($sql, [
            'id' => $result->getId(),
        ]);

        // Should be Australia/Melbourne TZ, +10 Hrs.
        /** @var array<string, string> $expected */
        $expected = [
            'updated_at' => '2022-05-19 10:00:00',
            'created_at' => '2022-05-19 10:00:00',
        ];

        /** @var array<string, string> $actual */
        $actual = [
            'updated_at' => (string) Arr::get($data, 'updated_at', ''),
            'created_at' => (string) Arr::get($data, 'created_at', ''),
        ];

        self::assertSame($expected, $actual);
        self::assertNotEmpty($result->getId());
    }
}
