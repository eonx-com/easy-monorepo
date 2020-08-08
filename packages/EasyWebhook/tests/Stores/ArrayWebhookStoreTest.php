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

        $result = $store->store(new WebhookResult(Webhook::fromArray(['method' => 'PUT', 'https://eonx.com'])));

        if ($result === null) {
            return;
        }

        self::assertInstanceOf(Webhook::class, $store->find((string)$result->getWebhook()->getId())->getWebhook());
    }
}
