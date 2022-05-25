<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Stores\DoctrineDbalStore;
use EonX\EasyWebhook\Tests\AbstractStoreTestCase;
use EonX\EasyWebhook\Webhook;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;

final class DoctrineDbalStoreFromIlluminateDatabaseTest extends AbstractStoreTestCase
{
    public function testStore(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $id = 'my-id';
        $store = new DoctrineDbalStore($this->getRandomGenerator(), $conn, $this->getDataCleaner());
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)->id($id);

        // Save new result with set id
        $store->store($webhook);
        // Update result
        $store->store($webhook);

        self::assertInstanceOf(WebhookInterface::class, $store->find($id));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testStoreWithTimezone(): void
    {
        // Time in UTC, this is at 00:00:00
        Carbon::setTestNow('2022-05-19');

        $conn = $this->getDoctrineDbalConnection();
        $id = 'my-id';
        $store = new DoctrineDbalStore(
            $this->getRandomGenerator(),
            $conn,
            $this->getDataCleaner(),
            'easy_webhooks',
            'Australia/Melbourne'
        );
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)
            ->id($id);

        // Save new result with set id
        $store->store($webhook);
        // Update result
        $store->store($webhook);

        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', 'easy_webhooks');

        /** @var mixed[] $data */
        $data = $conn->fetchAssociative($sql, [
            'id' => $id,
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

        self::assertEquals($expected, $actual);
    }

    protected function getDoctrineDbalConnection(): Connection
    {
        $dbManager = new Manager();
        $dbManager->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        return $this->doctrineDbal = $this->doctrineDbal ?? $dbManager->getConnection()
            ->getDoctrineConnection();
    }
}
