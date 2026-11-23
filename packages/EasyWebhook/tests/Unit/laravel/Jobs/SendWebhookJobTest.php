<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Laravel\Jobs;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Exception\WebhookHttpClientOptionNotAllowedException;
use EonX\EasyWebhook\Common\Exception\WebhookRequestFailedException;
use EonX\EasyWebhook\Common\Exception\WebhookResponseTooLargeException;
use EonX\EasyWebhook\Common\Store\ArrayStore;
use EonX\EasyWebhook\Common\Strategy\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Laravel\Jobs\SendWebhookJob;
use EonX\EasyWebhook\Tests\Stub\Client\WebhookClientStub;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;

final class SendWebhookJobTest extends AbstractUnitTestCase
{
    public function testItDoesNotReleaseTheJobWhenHttpClientOptionIsNotAllowed(): void
    {
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
        $webhook = $store->store(Webhook::create('https://eonx.com')->maxAttempt(3));
        $client = new WebhookClientStub(new WebhookHttpClientOptionNotAllowedException('not allowed'));
        $job = new SendWebhookJob((string) $webhook->getId(), 3)
            ->withFakeQueueInteractions();

        $job->handle($client, new MultiplierWebhookRetryStrategy(), $store);

        $job->assertNotReleased();
    }

    public function testItDoesNotReleaseTheJobWhenResponseIsTooLarge(): void
    {
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
        $webhook = $store->store(Webhook::create('https://eonx.com')->maxAttempt(3));
        // The HTTP client wraps the exception, so it is the previous one of the request failure
        $client = new WebhookClientStub(new WebhookRequestFailedException(
            message: 'failed',
            previous: new WebhookResponseTooLargeException('too large')
        ));
        $job = new SendWebhookJob((string) $webhook->getId(), 3)
            ->withFakeQueueInteractions();

        $job->handle($client, new MultiplierWebhookRetryStrategy(), $store);

        $job->assertNotReleased();
    }

    public function testItReleasesTheJobWhenRequestFailed(): void
    {
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
        $webhook = $store->store(Webhook::create('https://eonx.com')->maxAttempt(3));
        $client = new WebhookClientStub(new WebhookRequestFailedException('failed'));
        $job = new SendWebhookJob((string) $webhook->getId(), 3)
            ->withFakeQueueInteractions();

        $job->handle($client, new MultiplierWebhookRetryStrategy(), $store);

        $job->assertReleased();
    }
}
