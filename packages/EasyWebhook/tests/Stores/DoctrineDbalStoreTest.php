<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use Carbon\Carbon;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Stores\DoctrineDbalStore;
use EonX\EasyWebhook\Tests\AbstractStoreTestCase;
use EonX\EasyWebhook\Webhook;

final class DoctrineDbalStoreTest extends AbstractStoreTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestFindDueWebhooks(): iterable
    {
        yield '0 webhook in store' => [[], 0];

        yield '1 webhook in store but not sendAfter' => [[$this->createWebhookForSendAfter()], 0];

        yield '1 webhook in store and is sendAfter' => [
            [$this->createWebhookForSendAfter(Carbon::now('UTC')->subDay())],
            1,
        ];

        yield 'webhooks in store but only 1 is sendAfter' => [
            [
                $this->createWebhookForSendAfter(Carbon::now('UTC')->subDay()),
                $this->createWebhookForSendAfter(Carbon::now('UTC')->subDay(), WebhookInterface::STATUS_SUCCESS),
                $this->createWebhookForSendAfter(Carbon::now('UTC')->addDay()),
            ],
            1,
        ];
    }

    /**
     * @param \EonX\EasyWebhook\Interfaces\WebhookInterface[] $webhooks
     * @param int $expectedDue
     *
     * @throws \Doctrine\DBAL\Exception
     *
     * @dataProvider providerTestFindDueWebhooks
     */
    public function testFindDueWebhooks(array $webhooks, int $expectedDue): void
    {
        $store = $this->getStore();

        foreach ($webhooks as $webhook) {
            $store->store($webhook);
        }

        $dueWebhooks = $store->findDueWebhooks(new StartSizeData(1, 15));

        self::assertCount($expectedDue, $dueWebhooks->getItems());
    }

    public function testFindWebhook(): void
    {
        $store = $this->getStore();
        $webhook = Webhook::fromArray([
            WebhookInterface::OPTION_METHOD => WebhookInterface::DEFAULT_METHOD,
            WebhookInterface::OPTION_URL => 'https://eonx.com',
            WebhookInterface::OPTION_HTTP_OPTIONS => [
                'headers' => [
                    'X-My-Header' => 'my-header',
                ],
            ],
        ]);

        $store->store($webhook);

        self::assertInstanceOf(WebhookInterface::class, $store->find((string)$webhook->getId()));
        self::assertNull($store->find('invalid'));
    }

    public function testStore(): void
    {
        $id = 'my-id';
        $store = $this->getStore();
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)->id($id);

        // Save new result with set id
        $store->store($webhook);
        // Update result
        $store->store($webhook);

        self::assertInstanceOf(WebhookInterface::class, $store->find($id));
    }

    private function getStore(): DoctrineDbalStore
    {
        return new DoctrineDbalStore($this->getRandomGenerator(), $this->getDoctrineDbalConnection());
    }
}
