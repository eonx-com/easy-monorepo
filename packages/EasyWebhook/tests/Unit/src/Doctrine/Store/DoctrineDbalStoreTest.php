<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Doctrine\Store;

use Carbon\Carbon;
use DateTimeInterface;
use EonX\EasyPagination\Pagination\Pagination;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Enum\WebhookOption;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Doctrine\Store\DoctrineDbalStore;
use PHPUnit\Framework\Attributes\DataProvider;

final class DoctrineDbalStoreTest extends AbstractDoctrineDbalStoreTestCase
{
    /**
     * @see testFindDueWebhooks
     */
    public static function provideFindDueWebhooksData(): iterable
    {
        yield '0 webhook in store' => [[], 0];

        yield '1 webhook in store but not sendAfter' => [[self::createWebhookForSendAfter()], 0];

        yield '1 webhook in store and is sendAfter' => [
            [self::createWebhookForSendAfter(Carbon::now('UTC')->subDay())],
            1,
        ];

        yield 'webhooks in store but only 1 is sendAfter' => [
            [
                self::createWebhookForSendAfter(Carbon::now('UTC')->subDay()),
                self::createWebhookForSendAfter(Carbon::now('UTC')->subDay(), WebhookStatus::Success),
                self::createWebhookForSendAfter(Carbon::now('UTC')->addDay()),
            ],
            1,
        ];
    }

    /**
     * @see testFindDueWebhooksWithTimezone
     */
    public static function provideFindDueWebhooksWithTimezoneData(): iterable
    {
        // Pacific/Kiritimati is UTC+14: "now" there are 14 hours ahead of the UTC wall-clock
        $sendAfter = Carbon::parse('2026-06-02 12:00:00', 'UTC');

        yield 'now in UTC, send_after just ahead, not due' => [
            self::createWebhookForSendAfter(Carbon::now('UTC')->addMinute()),
            null,
            'UTC',
            0,
        ];

        yield 'now in UTC+14, send_after just ahead of UTC, due' => [
            self::createWebhookForSendAfter(Carbon::now('UTC')->addMinute()),
            null,
            'Pacific/Kiritimati',
            1,
        ];

        yield 'now in UTC+14, send_after just inside the window, due' => [
            self::createWebhookForSendAfter(Carbon::now('UTC')->addHours(14)->subMinute()),
            null,
            'Pacific/Kiritimati',
            1,
        ];

        yield 'now in UTC+14, send_after just past the window, not due' => [
            self::createWebhookForSendAfter(Carbon::now('UTC')->addHours(14)->addMinute()),
            null,
            'Pacific/Kiritimati',
            0,
        ];

        // Explicit $sendAfter: $timezone must not affect the result
        yield 'explicit sendAfter after send_after, due' => [
            self::createWebhookForSendAfter($sendAfter),
            $sendAfter->copy()
->addSecond(),
            'Pacific/Kiritimati',
            1,
        ];

        yield 'explicit sendAfter equal to send_after, not due' => [
            self::createWebhookForSendAfter($sendAfter),
            $sendAfter->copy(),
            'Pacific/Kiritimati',
            0,
        ];

        yield 'explicit sendAfter before send_after, not due' => [
            self::createWebhookForSendAfter($sendAfter),
            $sendAfter->copy()
->subSecond(),
            'Pacific/Kiritimati',
            0,
        ];
    }

    /**
     * @param \EonX\EasyWebhook\Common\Entity\WebhookInterface[] $webhooks
     */
    #[DataProvider('provideFindDueWebhooksData')]
    public function testFindDueWebhooks(array $webhooks, int $expectedDue): void
    {
        $store = $this->getStore();

        foreach ($webhooks as $webhook) {
            $store->store($webhook);
        }

        $dueWebhooks = $store->findDueWebhooks(new Pagination(1, 15));

        self::assertSame($expectedDue, $dueWebhooks->getTotalItems());
        self::assertCount($expectedDue, $dueWebhooks->getItems());
    }

    #[DataProvider('provideFindDueWebhooksWithTimezoneData')]
    public function testFindDueWebhooksWithTimezone(
        WebhookInterface $webhook,
        ?DateTimeInterface $sendAfter,
        string $timezone,
        int $expectedDue,
    ): void {
        $store = $this->getStore();
        $store->store($webhook);

        $dueWebhooks = $store->findDueWebhooks(new Pagination(1, 15), $sendAfter, $timezone);

        self::assertSame($expectedDue, $dueWebhooks->getTotalItems());
        self::assertCount($expectedDue, $dueWebhooks->getItems());
    }

    public function testFindWebhook(): void
    {
        $store = $this->getStore();
        $webhook = Webhook::fromArray([
            WebhookOption::Method->value => WebhookInterface::DEFAULT_METHOD,
            WebhookOption::Url->value => 'https://eonx.com',
            WebhookOption::HttpOptions->value => [
                'headers' => [
                    'X-My-Header' => 'my-header',
                ],
            ],
        ]);
        $webhook->extra([
            'extra_column' => 'value',
        ]);

        $store->store($webhook);
        $found = $store->find((string)$webhook->getId());

        self::assertNull($store->find('invalid'));
        self::assertInstanceOf(WebhookInterface::class, $found);

        if ($found !== null) {
            self::assertArrayHasKey('extra_column', $found->getExtra() ?? []);
            self::assertEquals('value', ($found->getExtra() ?? [])['extra_column']);
        }
    }

    public function testFindWebhookWithSendAfterContainingMicroseconds(): void
    {
        $store = $this->getStore();
        $webhook = self::createWebhookForSendAfter(Carbon::now('UTC'));
        $store->store($webhook);
        $connection = $this->getDoctrineDbalConnection();
        $connection->update(
            DoctrineDbalStore::DEFAULT_TABLE,
            ['send_after' => '2026-06-02 12:00:00.123456'],
            ['id' => $webhook->getId()]
        );

        $found = $store->find((string)$webhook->getId());

        self::assertInstanceOf(WebhookInterface::class, $found);
        self::assertSame('2026-06-02 12:00:00.123456', $found->getSendAfter()?->format('Y-m-d H:i:s.u'));
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
        return new DoctrineDbalStore(
            self::getRandomGenerator(),
            $this->getDoctrineDbalConnection(),
            $this->getDataCleaner()
        );
    }
}
