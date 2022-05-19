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

    public function testStoreWithTimezone(): void
    {
        // Time in UTC.
        Carbon::setTestNow('2022-05-19 01:00:00');

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

        $data = $conn->fetchAssociative($sql, [
            'id' => $result->getId(),
        ]);

        // Should be Australia/Melbourne TZ, +10 Hrs.
        $expected = [
            'updated_at' => '2022-05-19 11:00:00',
            'created_at' => '2022-05-19 11:00:00',
        ];

        $actual = [
            'updated_at' => Arr::get($data, 'updated_at'),
            'created_at' => Arr::get($data, 'created_at'),
        ];

        self::assertNotEmpty($result->getId());
        self::assertEquals($expected, $actual);
    }
}
