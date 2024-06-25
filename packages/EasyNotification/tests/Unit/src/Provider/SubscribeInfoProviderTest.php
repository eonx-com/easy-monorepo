<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Provider;

use EonX\EasyNotification\Provider\SubscribeInfoProvider;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use Nette\Utils\Json;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SubscribeInfoProviderTest extends AbstractUnitTestCase
{
    public function testProvide(): void
    {
        $response = new MockResponse(Json::encode([
            'jwt' => 'my-jwt',
            'topics' => ['/nathan'],
            'url' => 'https://subscribe.com',
        ]));
        $httpClient = new MockHttpClient([$response]);
        $subscribeInfoProvider = new SubscribeInfoProvider('https://my-url.com', $httpClient);

        $subscribeInfo = $subscribeInfoProvider->provide('my-api-key', 'my-provider', ['/nathan']);

        self::assertEquals('my-jwt', $subscribeInfo->getJwt());
        self::assertEquals(['/nathan'], $subscribeInfo->getTopics());
        self::assertEquals('https://subscribe.com', $subscribeInfo->getUrl());
    }
}
