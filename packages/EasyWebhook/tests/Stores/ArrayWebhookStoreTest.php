<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyWebhook\Stores\ArrayWebhookResultStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class ArrayWebhookStoreTest extends AbstractTestCase
{
    public function testFindWithDefaultClass(): void
    {
        $store = new ArrayWebhookResultStore((new RandomGenerator())->setUuidV4Generator(new RamseyUuidV4Generator()));
        $result = new WebhookResult(Webhook::fromArray([
            'method' => 'PUT',
            'https://eonx.com',
        ]));
        $result = $store->store($result);
        $found = $store->find((string)$result->getWebhook()->getId());

        if ($found === null) {
            return;
        }

        $results = $store->getResults();
        $store->reset();
        $resetResults = $store->getResults();

        self::assertInstanceOf(Webhook::class, $found->getWebhook());
        self::assertCount(1, $results);
        self::assertCount(0, $resetResults);
    }
}
