<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Doctrine\Store;

use Carbon\Carbon;
use EonX\EasyPagination\ValueObject\Pagination;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
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
                self::createWebhookForSendAfter(Carbon::now('UTC')->subDay(), WebhookInterface::STATUS_SUCCESS),
                self::createWebhookForSendAfter(Carbon::now('UTC')->addDay()),
            ],
            1,
        ];
    }

    /**
     * @param \EonX\EasyWebhook\Common\Entity\WebhookInterface[] $webhooks
     *
     * @throws \Doctrine\DBAL\Exception
     */
    #[DataProvider('provideFindDueWebhooksData')]
    public function testFindDueWebhooks(array $webhooks, int $expectedDue): void
    {
        $store = $this->getStore();

        foreach ($webhooks as $webhook) {
            $store->store($webhook);
        }

        $dueWebhooks = $store->findDueWebhooks(new Pagination(1, 15));

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
