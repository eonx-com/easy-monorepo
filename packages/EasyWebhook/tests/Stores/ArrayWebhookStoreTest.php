<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyWebhook\Stores\ArrayWebhookStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;

final class ArrayWebhookStoreTest extends AbstractTestCase
{
    public function testFindWithDefaultClass(): void
    {
        $store = new ArrayWebhookStore((new RandomGenerator())->setUuidV4Generator(new RamseyUuidV4Generator()));
        $data = ['method' => 'PUT', 'https://eonx.com'];

        $webhookId = $store->store($data);

        self::assertInstanceOf(Webhook::class, $store->find($webhookId));
    }
}
