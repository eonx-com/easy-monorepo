<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyWebhook\Webhook;

final class WebhookTest extends AbstractTestCase
{
    public function testSettersGetters(): void
    {
        $body = [
            'key' => 'value',
        ];
        $event = 'my-event';
        $extra = [
            'key' => 'value',
        ];
        $httpOptions = [
            'key' => 'value',
        ];
        $maxAttempt = 5;
        $secret = 'my-secret';
        $url = 'https://eonx.com';

        $webhook = Webhook::create($url, $body);
        $webhook
            ->body($body)
            ->event($event)
            ->extra($extra)
            ->httpClientOptions($httpOptions)
            ->maxAttempt($maxAttempt)
            ->mergeExtra([
                'key1' => 'value1',
            ])
            ->secret($secret)
            ->sendNow()
            ->url($url);

        self::assertEquals($body, $webhook->getBody());
        self::assertEquals($event, $webhook->getEvent());
        self::assertEquals($extra + [
            'key1' => 'value1',
        ], $webhook->getExtra());
        self::assertEquals($httpOptions, $webhook->getHttpClientOptions());
        self::assertEquals($maxAttempt, $webhook->getMaxAttempt());
        self::assertEquals($secret, $webhook->getSecret());
        self::assertTrue($webhook->isSendNow());
        self::assertEquals($url, $webhook->getUrl());
        self::assertIsArray($webhook->toArray());
    }
}
